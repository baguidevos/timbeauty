<?php

use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
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
        'name' => 'Admin Test',
        'email' => 'admin-view-supplier@test.com',
        'active' => true,
    ]);

    $this->supplier = Supplier::create([
        'name' => 'Barber Pro Supplies Togo',
        'contactName' => 'M. Kodjo Sylvain',
        'phone' => '+228 90 12 34 56',
        'email' => 'contact@barberpro-tg.com',
        'address' => 'Boulevard du 13 Janvier, Lomé',
        'paymentTerms' => 'À 30 jours',
        'notes' => 'Fournisseur principal pour les lames et cires.',
        'status' => 'active',
    ]);

    $this->category = ProductCategory::create([
        'name' => 'Soins Capillaires',
    ]);

    $this->product = Product::create([
        'name' => 'Cire Coiffante Mate 150ml',
        'reference' => 'CIR-MAT-150',
        'categoryId' => $this->category->id,
        'supplierId' => $this->supplier->id,
        'purchasePrice' => 3000,
        'sellingPrice' => 5000,
        'stockQuantity' => 5,
        'minStockLevel' => 10,
        'status' => 'active',
    ]);

    $this->purchaseOrder = PurchaseOrder::create([
        'reference' => 'BC-2026-0042',
        'supplierId' => $this->supplier->id,
        'orderDate' => now()->toDateString(),
        'expectedDate' => now()->addDays(5)->toDateString(),
        'status' => 'pending',
        'totalAmount' => 60000,
        'paidAmount' => 20000,
        'notes' => 'Livrer au salon avant 12h svp.',
        'createdBy' => $this->admin->id,
    ]);

    $this->orderItem = PurchaseOrderItem::create([
        'purchaseOrderId' => $this->purchaseOrder->id,
        'productId' => $this->product->id,
        'productName' => $this->product->name,
        'quantity' => 20,
        'unitPrice' => 3000,
        'totalPrice' => 60000,
        'receivedQuantity' => 0,
    ]);
});

it('can render enhanced supplier profile view with stats, orders and products', function () {
    $this->actingAs($this->admin);

    $test = Livewire::test(ViewSupplier::class, [
        'record' => $this->supplier->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Barber Pro Supplies Togo')
        ->assertSee('M. Kodjo Sylvain')
        ->assertSee('À 30 jours')
        ->assertSee('60 000 FCFA')
        ->assertSee('BC-2026-0042');

    $test->set('activeTab', 'products')
        ->assertSee('Cire Coiffante Mate 150ml');
});

it('can update supplier notes directly from view page', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewSupplier::class, [
        'record' => $this->supplier->getRouteKey(),
    ])
        ->set('supplierNotes', 'Nouvelles conditions négociées : remise 5% dès 100 000 FCFA.')
        ->call('saveNotes');

    expect($this->supplier->fresh()->notes)->toBe('Nouvelles conditions négociées : remise 5% dès 100 000 FCFA.');
});

it('can render enhanced purchase order view with details and items', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewPurchaseOrder::class, [
        'record' => $this->purchaseOrder->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSee('BC-2026-0042')
        ->assertSee('Barber Pro Supplies Togo')
        ->assertSee('60 000 FCFA')
        ->assertSee('Cire Coiffante Mate 150ml')
        ->assertSee('20')
        ->assertSee('Livrer au salon avant 12h svp.');
});

it('can mark purchase order as ordered and receive stock updating inventory and stock movements', function () {
    $this->actingAs($this->admin);

    $initialStock = $this->product->stockQuantity; // 5

    $component = Livewire::test(ViewPurchaseOrder::class, [
        'record' => $this->purchaseOrder->getRouteKey(),
    ]);

    // 1. Mark as Ordered
    $component->callAction('markAsOrdered');
    expect($this->purchaseOrder->fresh()->status)->toBe('ordered');

    // 2. Receive Order & Update Stock
    $component->callAction('receiveOrder');

    $freshOrder = $this->purchaseOrder->fresh();
    $freshProduct = $this->product->fresh();
    $freshItem = $this->orderItem->fresh();

    expect($freshOrder->status)->toBe('received')
        ->and($freshOrder->receivedDate)->not->toBeNull()
        ->and($freshItem->receivedQuantity)->toBe(20)
        ->and($freshProduct->stockQuantity)->toBe($initialStock + 20); // 5 + 20 = 25

    // Verify StockMovement entry
    $movement = StockMovement::where('productId', $this->product->id)
        ->where('reference', $this->purchaseOrder->reference)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('in')
        ->and($movement->quantity)->toBe(20);
});
