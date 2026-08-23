<?php

use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Purchase',
        'email' => 'admin-purchase@test.com',
        'active' => true,
    ]);

    $this->supplier = Supplier::create([
        'name' => 'Distributeur Beauté Pro',
        'phone' => '+228 90 11 22 33',
        'email' => 'contact@beaute-pro.com',
        'address' => 'Lomé, Togo',
    ]);

    $this->category = ProductCategory::create([
        'name' => 'Soins du Visage',
    ]);

    $this->product1 = Product::create([
        'name' => 'Gel Nettoyant Purifiant',
        'sellingPrice' => 5000,
        'purchasePrice' => 3000,
        'stockQuantity' => 5,
        'minStockLevel' => 2,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->product2 = Product::create([
        'name' => 'Crème Hydratante Apaisante',
        'sellingPrice' => 8000,
        'purchasePrice' => 4500,
        'stockQuantity' => 2,
        'minStockLevel' => 1,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->order = PurchaseOrder::create([
        'reference' => 'CMD-TEST-001',
        'supplierId' => $this->supplier->id,
        'orderDate' => now()->toDateString(),
        'status' => 'ordered',
        'totalAmount' => 37500, // (5 * 3000) + (5 * 4500)
        'paidAmount' => 0,
        'createdBy' => $this->admin->id,
    ]);

    $this->item1 = PurchaseOrderItem::create([
        'purchaseOrderId' => $this->order->id,
        'productId' => $this->product1->id,
        'productName' => $this->product1->name,
        'quantity' => 5,
        'unitPrice' => 3000,
        'totalPrice' => 15000,
        'receivedQuantity' => 0,
    ]);

    $this->item2 = PurchaseOrderItem::create([
        'purchaseOrderId' => $this->order->id,
        'productId' => $this->product2->id,
        'productName' => $this->product2->name,
        'quantity' => 5,
        'unitPrice' => 4500,
        'totalPrice' => 22500,
        'receivedQuantity' => 0,
    ]);
});

it('can receive a purchase order in full with 100% conforming toggle', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('receiveOrder', [
            'is_conforming' => true,
        ])
        ->assertHasNoActionErrors();

    // Check stocks incremented by full quantity
    $this->product1->refresh();
    $this->product2->refresh();
    expect($this->product1->stockQuantity)->toBe(10) // 5 + 5
        ->and($this->product2->stockQuantity)->toBe(7); // 2 + 5

    // Check StockMovements
    expect(StockMovement::where('productId', $this->product1->id)->where('type', 'in')->count())->toBe(1)
        ->and(StockMovement::where('productId', $this->product2->id)->where('type', 'in')->count())->toBe(1);

    // Check order status
    $this->order->refresh();
    expect($this->order->status)->toBe('received')
        ->and($this->order->isReceived())->toBeTrue();
});

it('can receive a purchase order partially with adjusted quantities per item', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('receiveOrder', [
            'is_conforming' => false,
            'received_items' => [
                $this->item1->id => 3, // 3 received out of 5
                $this->item2->id => 5, // 5 received out of 5
            ],
        ])
        ->assertHasNoActionErrors();

    // Check stocks
    $this->product1->refresh();
    $this->product2->refresh();
    expect($this->product1->stockQuantity)->toBe(8) // 5 + 3
        ->and($this->product2->stockQuantity)->toBe(7); // 2 + 5

    // Check items received quantities
    $this->item1->refresh();
    $this->item2->refresh();
    expect($this->item1->receivedQuantity)->toBe(3)
        ->and($this->item2->receivedQuantity)->toBe(5);

    // Check order is partially received
    $this->order->refresh();
    expect($this->order->status)->toBe('partially_received')
        ->and($this->order->isPartiallyReceived())->toBeTrue();

    // Now complete the remaining 2 items of product 1
    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('receiveOrder', [
            'is_conforming' => true,
        ])
        ->assertHasNoActionErrors();

    $this->product1->refresh();
    expect($this->product1->stockQuantity)->toBe(10); // 8 + 2 additional

    $this->order->refresh();
    expect($this->order->status)->toBe('received');
});
