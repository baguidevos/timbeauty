<?php

use App\Filament\Resources\Sales\Pages\ViewSale;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\CashRegister;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StockMovement;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin-view-sale@test.com',
        'active' => true,
    ]);

    $this->tier = LoyaltyTier::create([
        'name' => 'Gold VIP',
        'minPoints' => 500,
        'pointsPerFCFA' => 0.002,
        'discountPercentage' => 10,
        'color' => '#FFD700',
        'status' => 'active',
    ]);

    $this->client = Client::create([
        'firstName' => 'Kodjo',
        'lastName' => 'Agbeko',
        'phone' => '+228 90 12 34 56',
        'loyaltyTierId' => $this->tier->id,
        'loyaltyPoints' => 450,
        'totalVisits' => 6,
        'totalSpent' => 25000,
        'firstVisitDate' => now()->subMonths(2)->toDateString(),
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Marc',
        'lastName' => 'Koffi',
        'phone' => '+228 91 22 33 44',
        'status' => 'active',
        'canPerformServices' => true,
    ]);

    $this->serviceCategory = ServiceCategory::create([
        'name' => 'Coiffure Homme',
        'order' => 1,
    ]);

    $this->service = Service::create([
        'name' => 'Coupe & Dégradé Laser',
        'price' => 4000,
        'duration' => 30,
        'categoryId' => $this->serviceCategory->id,
        'status' => 'active',
    ]);

    $this->productCategory = ProductCategory::create([
        'name' => 'Soins Capillaires',
    ]);

    $this->product = Product::create([
        'name' => 'Cire Mate 100ml',
        'reference' => 'CIR-100',
        'categoryId' => $this->productCategory->id,
        'purchasePrice' => 2000,
        'sellingPrice' => 3500,
        'stockQuantity' => 10,
        'minStockLevel' => 2,
        'status' => 'active',
    ]);

    $this->cashRegister = CashRegister::create([
        'status' => 'open',
        'openingAmount' => 15000,
        'openedAt' => now(),
        'openedBy' => $this->admin->id,
    ]);

    $this->appointment = Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => now()->toDateString(),
        'startTime' => '10:00',
        'endTime' => '10:30',
        'status' => 'completed',
    ]);

    $this->promo = Promotion::create([
        'name' => 'Remise Flash 1500 F',
        'type' => 'fixed',
        'value' => 1500,
        'status' => 'active',
    ]);

    $this->sale = Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'appointmentId' => $this->appointment->id,
        'cashRegisterId' => $this->cashRegister->id,
        'promotionId' => $this->promo->id,
        'subtotal' => 7500,
        'discountAmount' => 1500,
        'total' => 6000,
        'paymentMethod' => 'tmoney',
        'status' => 'completed',
        'notes' => 'Client très satisfait du dégradé.',
        'createdBy' => $this->admin->id,
    ]);

    SaleItem::create([
        'saleId' => $this->sale->id,
        'type' => 'service',
        'itemId' => $this->service->id,
        'name' => $this->service->name,
        'quantity' => 1,
        'unitPrice' => 4000,
        'discount' => 0,
        'total' => 4000,
    ]);

    SaleItem::create([
        'saleId' => $this->sale->id,
        'type' => 'product',
        'itemId' => $this->product->id,
        'name' => $this->product->name,
        'quantity' => 1,
        'unitPrice' => 3500,
        'discount' => 0,
        'total' => 3500,
    ]);

    $this->promo->categories()->attach($this->serviceCategory->id);
});

it('can render enhanced sale detail view with full breakdown and actors', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewSale::class, [
        'record' => $this->sale->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Reçu de Vente #'.$this->sale->id)
        ->assertSee('Kodjo Agbeko')
        ->assertSee('Marc Koffi')
        ->assertSee('Admin Test')
        ->assertSee('Coupe & Dégradé Laser')
        ->assertSee('Coiffure Homme')
        ->assertSee('Promo -1 500 FCFA')
        ->assertSee('Cire Mate 100ml')
        ->assertSee('7 500 FCFA')
        ->assertSee('6 000 FCFA')
        ->assertSee('Remise Flash 1500 F')
        ->assertSee('Client très satisfait du dégradé.');
});

it('can cancel completed sale and restore product stock', function () {
    $this->actingAs($this->admin);

    $initialStock = $this->product->stockQuantity; // 10

    Livewire::test(ViewSale::class, [
        'record' => $this->sale->getRouteKey(),
    ])
        ->callAction('cancelSale');

    expect($this->sale->fresh()->status)->toBe('cancelled')
        ->and($this->product->fresh()->stockQuantity)->toBe($initialStock + 1); // 10 + 1 = 11

    $movement = StockMovement::where('productId', $this->product->id)
        ->where('reference', 'CANCEL-SALE-'.$this->sale->id)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('in')
        ->and($movement->quantity)->toBe(1);
});

it('displays manual discount message when discount exists without a promotion', function () {
    $this->actingAs($this->admin);

    $manualDiscountSale = Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'cashRegisterId' => $this->cashRegister->id,
        'subtotal' => 5000,
        'discountAmount' => 1000,
        'total' => 4000,
        'paymentMethod' => 'cash',
        'status' => 'completed',
        'promotionId' => null,
        'createdBy' => $this->admin->id,
    ]);

    Livewire::test(ViewSale::class, [
        'record' => $manualDiscountSale->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Remise Manuelle en Caisse')
        ->assertSee('- 1 000 FCFA');
});
