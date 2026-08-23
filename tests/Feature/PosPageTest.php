<?php

use App\Filament\Pages\Pos;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin POS',
        'email' => 'admin-pos@test.com',
        'active' => true,
    ]);

    $this->category = ServiceCategory::create([
        'name' => 'Coupe Test',
        'order' => 1,
    ]);

    $this->service = Service::create([
        'name' => 'Coupe Homme Test',
        'price' => 3500,
        'duration' => 30,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->productCategory = ProductCategory::create([
        'name' => 'Produits Capillaires',
    ]);

    $this->product = Product::create([
        'name' => 'Gel Test',
        'sellingPrice' => 2000,
        'stockQuantity' => 15,
        'minStockLevel' => 3,
        'categoryId' => $this->productCategory->id,
        'status' => 'active',
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Barber',
        'lastName' => 'Test',
        'phone' => '+228 90 00 00 01',
        'status' => 'active',
        'canPerformServices' => true,
    ]);

    $this->client = Client::create([
        'firstName' => 'Client',
        'lastName' => 'Test',
        'phone' => '+228 90 00 00 02',
        'firstVisitDate' => now()->toDateString(),
    ]);
});

it('can render POS page for authenticated user', function () {
    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->assertSuccessful()
        ->assertSee('Point de Vente')
        ->assertSee('Prestations')
        ->assertSee('Produits')
        ->assertSee($this->service->name);
});

it('can add service and product to cart and persist in session', function () {
    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id)
        ->call('addToCart', 'product', $this->product->id)
        ->assertSet('cart.service_'.$this->service->id.'.quantity', 1)
        ->assertSet('cart.product_'.$this->product->id.'.quantity', 1);

    expect(session('pos_cart.cart'))->toHaveKey('service_'.$this->service->id)
        ->and(session('pos_cart.cart'))->toHaveKey('product_'.$this->product->id);
});

it('can restore cart from session on mount', function () {
    session(['pos_cart' => [
        'cart' => [
            'service_'.$this->service->id => [
                'type' => 'service',
                'itemId' => $this->service->id,
                'name' => $this->service->name,
                'unitPrice' => 3500,
                'quantity' => 2,
            ],
        ],
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'discountType' => 'percentage',
        'discountValue' => 10,
        'paymentMethod' => 'tmoney',
        'notes' => 'Test note',
    ]]);

    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->assertSet('clientId', $this->client->id)
        ->assertSet('barberId', $this->barber->id)
        ->assertSet('paymentMethod', 'tmoney')
        ->assertSet('discountValue', 10)
        ->assertSet('cart.service_'.$this->service->id.'.quantity', 2);
});

it('can update quantity and remove item from cart', function () {
    $this->actingAs($this->admin);

    $test = Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id)
        ->call('updateQuantity', 'service_'.$this->service->id, 1)
        ->assertSet('cart.service_'.$this->service->id.'.quantity', 2);

    expect(session('pos_cart.cart.service_'.$this->service->id.'.quantity'))->toBe(2);

    $test->call('removeFromCart', 'service_'.$this->service->id)
        ->assertSet('cart', []);

    expect(session('pos_cart.cart'))->toBeEmpty();
});

it('can clear cart and forget session', function () {
    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id)
        ->call('clearCart')
        ->assertSet('cart', []);

    expect(session()->has('pos_cart'))->toBeFalse();
});

it('can update discount values without errors', function () {
    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id)
        ->set('discountType', 'percentage')
        ->set('discountValue', '15')
        ->assertSet('discountValue', '15')
        ->set('discountValue', '')
        ->set('discountValue', 20)
        ->assertSet('discountValue', 20);

    expect(session('pos_cart.discountValue'))->toBe(20.0);
});

it('can create quick client directly in POS', function () {
    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->set('quickClientFirstName', 'Koffi')
        ->set('quickClientLastName', 'Agbeko')
        ->set('quickClientPhone', '+228 99 88 77 66')
        ->call('createQuickClient')
        ->assertHasNoErrors()
        ->assertSet('showQuickClientModal', false);

    $newClient = Client::where('phone', '+228 99 88 77 66')->first();
    expect($newClient)->not->toBeNull()
        ->and($newClient->firstName)->toBe('Koffi');
});

it('can process sale, decrement product stock and clear session cart', function () {
    $initialStock = $this->product->stockQuantity;

    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->set('clientId', $this->client->id)
        ->set('barberId', $this->barber->id)
        ->call('addToCart', 'service', $this->service->id)
        ->call('addToCart', 'product', $this->product->id)
        ->set('paymentMethod', 'cash')
        ->call('processSale')
        ->assertHasNoErrors()
        ->assertSet('cart', [])
        ->assertSet('showReceiptModal', true);

    expect(session()->has('pos_cart'))->toBeFalse();

    $this->product->refresh();
    expect($this->product->stockQuantity)->toBe($initialStock - 1);

    $sale = Sale::latest()->first();
    expect($sale)->not->toBeNull()
        ->and($sale->total)->toEqual(5500)
        ->and($sale->items()->count())->toBe(2);
});

it('can load appointment into POS and process checkout with linked sale', function () {
    $this->actingAs($this->admin);

    $appointment = Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => now()->toDateString(),
        'startTime' => '14:00',
        'endTime' => '14:30',
        'status' => 'confirmed',
        'notes' => 'Coupe dégradé',
    ]);

    expect($appointment->isPaid())->toBeFalse();

    Livewire::test(Pos::class)
        ->call('loadAppointment', $appointment->id)
        ->assertSet('appointmentId', $appointment->id)
        ->assertSet('clientId', $this->client->id)
        ->assertSet('barberId', $this->barber->id)
        ->assertSet('cart.service_'.$this->service->id.'.quantity', 1)
        ->call('processSale')
        ->assertHasNoErrors();

    $sale = Sale::latest()->first();
    expect($sale->appointmentId)->toBe($appointment->id);

    $appointment->refresh();
    expect($appointment->status)->toBe('completed')
        ->and($appointment->isPaid())->toBeTrue()
        ->and($appointment->sale->id)->toBe($sale->id);
});

it('can unlink appointment from POS cart', function () {
    $this->actingAs($this->admin);

    $appointment = Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => now()->toDateString(),
        'startTime' => '15:00',
        'endTime' => '15:30',
        'status' => 'confirmed',
    ]);

    Livewire::test(Pos::class)
        ->call('loadAppointment', $appointment->id)
        ->assertSet('appointmentId', $appointment->id)
        ->call('unlinkAppointment')
        ->assertSet('appointmentId', null);

    expect(session('pos_cart.appointmentId'))->toBeNull();
});

it('can select an active promotion in POS, calculate discount, and record usage upon checkout', function () {
    $this->actingAs($this->admin);

    $promo = Promotion::create([
        'name' => 'Offre Rentrée 20%',
        'type' => 'percentage',
        'value' => 20,
        'status' => 'active',
        'currentUsages' => 0,
        'maxUsages' => 50,
    ]);

    Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id) // 3500 FCFA
        ->set('clientId', $this->client->id)
        ->call('applyPromotion', $promo->id)
        ->assertSet('selectedPromotionId', $promo->id)
        ->assertSet('discountType', 'percentage')
        ->assertSet('discountValue', 20)
        ->assertSee('Offre Rentrée 20%')
        ->call('processSale')
        ->assertHasNoErrors();

    $sale = Sale::latest()->first();
    expect((float) $sale->subtotal)->toBe(3500.0)
        ->and((float) $sale->discountAmount)->toBe(700.0) // 20% of 3500
        ->and((float) $sale->total)->toBe(2800.0);

    // Verify PromotionUsage record
    $usage = PromotionUsage::where('saleId', $sale->id)->first();
    expect($usage)->not->toBeNull()
        ->and($usage->promotionId)->toBe($promo->id)
        ->and($usage->clientId)->toBe($this->client->id);

    // Verify incremented usages
    expect($promo->fresh()->currentUsages)->toBe(1);
});

it('can remove an applied promotion from POS cart', function () {
    $this->actingAs($this->admin);

    $promo = Promotion::create([
        'name' => 'Réduction 1000 FCFA',
        'type' => 'fixed',
        'value' => 1000,
        'status' => 'active',
    ]);

    Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id)
        ->call('applyPromotion', $promo->id)
        ->assertSet('selectedPromotionId', $promo->id)
        ->assertSet('discountValue', 1000)
        ->call('removePromotion')
        ->assertSet('selectedPromotionId', null)
        ->assertSet('discountValue', 0);
});

it('only applies promotion discount to the specific assigned categories in cart', function () {
    $this->actingAs($this->admin);

    // Create Category B and Service B (5000 FCFA)
    $categoryB = ServiceCategory::create([
        'name' => 'Soins Test',
        'order' => 2,
    ]);

    $serviceB = Service::create([
        'name' => 'Soin Barbe Deluxe',
        'price' => 5000,
        'duration' => 45,
        'categoryId' => $categoryB->id,
        'status' => 'active',
    ]);

    // Create a promo of 50% only assigned to Category A ($this->category)
    $targetedPromo = Promotion::create([
        'name' => 'Flash 50% Coupe Homme',
        'type' => 'percentage',
        'value' => 50,
        'status' => 'active',
    ]);
    $targetedPromo->categories()->attach($this->category->id);

    // Add Service A (3500 FCFA, Cat A), Service B (5000 FCFA, Cat B), and Product (2000 FCFA) -> Subtotal = 10500 FCFA
    $test = Livewire::test(Pos::class)
        ->call('addToCart', 'service', $this->service->id)
        ->call('addToCart', 'service', $serviceB->id)
        ->call('addToCart', 'product', $this->product->id)
        ->call('applyPromotion', $targetedPromo->id);

    // Subtotal = 10500, but discount must only be 50% of 3500 = 1750 (NOT 50% of 10500 = 5250)
    expect($test->instance()->getSubtotal())->toBe(10500.0)
        ->and($test->instance()->getDiscountAmount())->toBe(1750.0)
        ->and($test->instance()->getTotal())->toBe(8750.0);

    $test->call('processSale')->assertHasNoErrors();

    $sale = Sale::latest()->first();
    expect((float) $sale->subtotal)->toBe(10500.0)
        ->and((float) $sale->discountAmount)->toBe(1750.0)
        ->and((float) $sale->total)->toBe(8750.0);

    // If cart has only Service B (Cat B), targeted promo on Cat A must give 0 FCFA discount
    $test2 = Livewire::test(Pos::class)
        ->call('addToCart', 'service', $serviceB->id)
        ->call('applyPromotion', $targetedPromo->id);

    expect($test2->instance()->getSubtotal())->toBe(5000.0)
        ->and($test2->instance()->getDiscountAmount())->toBe(0.0)
        ->and($test2->instance()->getTotal())->toBe(5000.0);
});

it('displays promo badge on service cards in POS when a valid promotion is active', function () {
    $this->actingAs($this->admin);

    $promo = Promotion::create([
        'name' => 'Flash -30% Coupe',
        'type' => 'percentage',
        'value' => 30,
        'status' => 'active',
    ]);
    $promo->categories()->attach($this->category->id);

    Livewire::test(Pos::class)
        ->assertSuccessful()
        ->assertSee('-30%')
        ->assertSee('Promo active (-30%)');
});
