<?php

use App\Filament\Pages\Pos;
use App\Filament\Resources\Promotions\Pages\CreatePromotion;
use App\Filament\Resources\Promotions\Pages\ListPromotions;
use App\Models\Client;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Promo',
        'email' => 'admin-promo@test.com',
        'active' => true,
    ]);

    $this->catHair = ServiceCategory::create([
        'name' => 'Coiffure & Coupe',
        'order' => 1,
    ]);

    $this->catBeard = ServiceCategory::create([
        'name' => 'Barbe & Rasage',
        'order' => 2,
    ]);

    $this->catCare = ServiceCategory::create([
        'name' => 'Soins du Visage',
        'order' => 3,
    ]);

    $this->serviceHair = Service::create([
        'name' => 'Coupe Dégradé',
        'price' => 3000,
        'duration' => 30,
        'categoryId' => $this->catHair->id,
        'status' => 'active',
    ]);

    $this->serviceBeard = Service::create([
        'name' => 'Taille de Barbe & Huile',
        'price' => 2000,
        'duration' => 20,
        'categoryId' => $this->catBeard->id,
        'status' => 'active',
    ]);

    $this->serviceCare = Service::create([
        'name' => 'Masque Purifiant Argile',
        'price' => 4000,
        'duration' => 25,
        'categoryId' => $this->catCare->id,
        'status' => 'active',
    ]);

    $this->client = Client::create([
        'firstName' => 'Michel',
        'lastName' => 'Ajavon',
        'phone' => '+228 90 99 88 77',
        'firstVisitDate' => now()->toDateString(),
    ]);
});

it('can list promotions and see targeted category badges in table', function () {
    $this->actingAs($this->admin);

    $promo = Promotion::create([
        'name' => 'Pack Gentleman',
        'type' => 'percentage',
        'value' => 25,
        'status' => 'active',
    ]);
    $promo->categories()->attach([$this->catHair->id, $this->catBeard->id]);

    $globalPromo = Promotion::create([
        'name' => 'Black Friday',
        'type' => 'fixed',
        'value' => 1000,
        'status' => 'active',
    ]);

    Livewire::test(ListPromotions::class)
        ->assertSuccessful()
        ->assertSee('Pack Gentleman')
        ->assertSee('2 catégorie(s) : Coiffure & Coupe, Barbe & Rasage')
        ->assertSee('Black Friday')
        ->assertSee('Toutes les catégories');
});

it('can create a promotion linked to specific categories via Filament resource', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreatePromotion::class)
        ->fillForm([
            'name' => 'Special Barbe & Soins',
            'type' => 'percentage',
            'value' => 30,
            'categories' => [$this->catBeard->id, $this->catCare->id],
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $promo = Promotion::where('name', 'Special Barbe & Soins')->first();
    expect($promo)->not->toBeNull()
        ->and($promo->categories->pluck('id')->toArray())->toEqualCanonicalizing([$this->catBeard->id, $this->catCare->id]);

    // Verify reverse relationship on ServiceCategory
    expect($this->catBeard->fresh()->promotions->pluck('id'))->toContain($promo->id)
        ->and($this->catCare->fresh()->promotions->pluck('id'))->toContain($promo->id)
        ->and($this->catHair->fresh()->promotions->pluck('id'))->not->toContain($promo->id);
});

it('applies category-targeted percentage discount in POS to services of that category only', function () {
    $this->actingAs($this->admin);

    $promo = Promotion::create([
        'name' => 'Promo Cheveux -50%',
        'type' => 'percentage',
        'value' => 50,
        'status' => 'active',
    ]);
    $promo->categories()->attach($this->catHair->id);

    // Cart with Hair ($3000) and Beard ($2000) -> Subtotal = $5000
    // Discount should be 50% of Hair only ($1500), Total = $3500
    $test = Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->serviceHair->id)
        ->call('addToCart', 'service', $this->serviceBeard->id)
        ->call('applyPromotion', $promo->id);

    expect($test->instance()->getSubtotal())->toBe(5000.0)
        ->and($test->instance()->getDiscountAmount())->toBe(1500.0)
        ->and($test->instance()->getTotal())->toBe(3500.0);
});

it('applies category-targeted free_service promotion to eligible category services in POS', function () {
    $this->actingAs($this->admin);

    $freePromo = Promotion::create([
        'name' => 'Barbe Offerte',
        'type' => 'free_service',
        'value' => 0,
        'status' => 'active',
    ]);
    $freePromo->categories()->attach($this->catBeard->id);

    // Cart with Hair ($3000) and Beard ($2000) -> Subtotal = $5000
    // Free service on Beard category gives $2000 off -> Total = $3000
    $test = Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->serviceHair->id)
        ->call('addToCart', 'service', $this->serviceBeard->id)
        ->call('applyPromotion', $freePromo->id);

    expect($test->instance()->getSubtotal())->toBe(5000.0)
        ->and($test->instance()->getDiscountAmount())->toBe(2000.0)
        ->and($test->instance()->getTotal())->toBe(3000.0);
});

it('applies global promotion to all categories when no category is specified', function () {
    $this->actingAs($this->admin);

    $globalPromo = Promotion::create([
        'name' => 'Remise Anniversaire 10%',
        'type' => 'percentage',
        'value' => 10,
        'status' => 'active',
    ]);
    // No categories attached -> applies to all services

    $test = Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->serviceHair->id)   // 3000
        ->call('addToCart', 'service', $this->serviceBeard->id)  // 2000
        ->call('addToCart', 'service', $this->serviceCare->id)   // 4000
        ->call('applyPromotion', $globalPromo->id);

    // Total subtotal = 9000, 10% of 9000 = 900, Total = 8100
    expect($test->instance()->getSubtotal())->toBe(9000.0)
        ->and($test->instance()->getDiscountAmount())->toBe(900.0)
        ->and($test->instance()->getTotal())->toBe(8100.0);
});
