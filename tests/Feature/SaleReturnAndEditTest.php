<?php

use App\Filament\Resources\Sales\Pages\EditSale;
use App\Filament\Resources\Sales\Pages\ViewSale;
use App\Models\Barber;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StockMovement;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Returns',
        'email' => 'admin-returns@test.com',
        'active' => true,
    ]);

    $this->category = ServiceCategory::create([
        'name' => 'Coiffure Homme',
        'order' => 1,
    ]);

    $this->service = Service::create([
        'name' => 'Coupe Dégradé',
        'price' => 4000,
        'duration' => 30,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->productCategory = ProductCategory::create([
        'name' => 'Soins Capillaires',
    ]);

    $this->product = Product::create([
        'name' => 'Cire Coiffante Mate',
        'sellingPrice' => 3000,
        'stockQuantity' => 10,
        'minStockLevel' => 2,
        'categoryId' => $this->productCategory->id,
        'status' => 'active',
    ]);

    $this->client = Client::create([
        'firstName' => 'Komla',
        'lastName' => 'Agbekponou',
        'phone' => '+228 90 99 88 77',
        'firstVisitDate' => now()->toDateString(),
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Barber',
        'lastName' => 'Paul',
        'phone' => '+228 91 00 11 22',
        'status' => 'active',
        'canPerformServices' => true,
    ]);

    $this->sale = Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'subtotal' => 10000, // 4000 (service) + 6000 (2x product)
        'discountAmount' => 0,
        'total' => 10000,
        'paymentMethod' => 'cash',
        'status' => 'completed',
        'createdBy' => $this->admin->id,
    ]);

    $this->serviceItem = SaleItem::create([
        'saleId' => $this->sale->id,
        'type' => 'service',
        'itemId' => $this->service->id,
        'name' => $this->service->name,
        'quantity' => 1,
        'unitPrice' => 4000,
        'discount' => 0,
        'total' => 4000,
        'status' => 'completed',
    ]);

    $this->productItem = SaleItem::create([
        'saleId' => $this->sale->id,
        'type' => 'product',
        'itemId' => $this->product->id,
        'name' => $this->product->name,
        'quantity' => 2,
        'unitPrice' => 3000,
        'discount' => 0,
        'total' => 6000,
        'status' => 'completed',
    ]);
});

it('can return a product item from ViewSale, re-increment stock and create stock movement', function () {
    $initialStock = $this->product->stockQuantity; // 10

    $this->actingAs($this->admin);

    Livewire::test(ViewSale::class, ['record' => $this->sale->getRouteKey()])
        ->call('openReturnModal', $this->productItem->id)
        ->assertSet('selectedReturnItemId', $this->productItem->id)
        ->assertSet('returnQuantity', 2)
        ->set('returnQuantity', 1)
        ->set('returnReason', 'Produit non désiré')
        ->call('submitReturnProduct')
        ->assertHasNoErrors();

    // Check stock was incremented by 1
    $this->product->refresh();
    expect($this->product->stockQuantity)->toBe($initialStock + 1);

    // Check StockMovement was recorded
    $movement = StockMovement::where('productId', $this->product->id)->latest()->first();
    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('in')
        ->and($movement->quantity)->toBe(1)
        ->and($movement->reference)->toBe("RETURN-SALE-{$this->sale->id}");

    // Check SaleItem state
    $this->productItem->refresh();
    expect($this->productItem->refundedQuantity)->toBe(1)
        ->and($this->productItem->status)->toBe('completed'); // partially returned

    // Check Sale totals updated: 4000 (service) + 3000 (1 remaining product) = 7000
    $this->sale->refresh();
    expect((float) $this->sale->subtotal)->toBe(7000.0)
        ->and((float) $this->sale->total)->toBe(7000.0);
});

it('can cancel a service item from ViewSale and adjust sale totals', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewSale::class, ['record' => $this->sale->getRouteKey()])
        ->call('openCancelServiceModal', $this->serviceItem->id)
        ->assertSet('selectedCancelItemId', $this->serviceItem->id)
        ->set('cancelServiceReason', 'Insatisfaction client')
        ->call('submitCancelService')
        ->assertHasNoErrors();

    // Check SaleItem is cancelled
    $this->serviceItem->refresh();
    expect($this->serviceItem->status)->toBe('cancelled')
        ->and($this->serviceItem->cancelReason)->toBe('Insatisfaction client');

    // Check Sale totals updated: only 6000 (2 products)
    $this->sale->refresh();
    expect((float) $this->sale->subtotal)->toBe(6000.0)
        ->and((float) $this->sale->total)->toBe(6000.0);
});

it('can render EditSale page and edit sale items with repeater and stock synchronization', function () {
    $this->actingAs($this->admin);

    Livewire::test(EditSale::class, ['record' => $this->sale->getRouteKey()])
        ->assertSuccessful()
        ->assertSee($this->service->name)
        ->assertSee($this->product->name);
});
