<?php

use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
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

    // 1. Mark as Ordered with optional advance deposit (+10 000 FCFA)
    $component->callAction('markAsOrdered', [
        'record_payment' => true,
        'payment_mode' => 'partial',
        'amount' => 10000,
    ]);
    expect($this->purchaseOrder->fresh()->status)->toBe('ordered')
        ->and((float) $this->purchaseOrder->fresh()->paidAmount)->toBe(30000.0); // 20 000 initial + 10 000 = 30 000

    // 2. Receive Order & Update Stock with full settlement (+30 000 FCFA)
    $component->callAction('receiveOrder', [
        'is_conforming' => true,
        'record_payment' => true,
        'payment_mode' => 'full',
        'amount' => 30000,
    ]);

    $freshOrder = $this->purchaseOrder->fresh();
    $freshProduct = $this->product->fresh();
    $freshItem = $this->orderItem->fresh();

    expect($freshOrder->status)->toBe('received')
        ->and($freshOrder->receivedDate)->not->toBeNull()
        ->and($freshItem->receivedQuantity)->toBe(20)
        ->and($freshProduct->stockQuantity)->toBe($initialStock + 20) // 5 + 20 = 25
        ->and((float) $freshOrder->paidAmount)->toBe(60000.0)
        ->and($freshOrder->isFullyPaid())->toBeTrue();

    // Verify StockMovement entry
    $movement = StockMovement::where('productId', $this->product->id)
        ->where('reference', $this->purchaseOrder->reference)
        ->first();

    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('in')
        ->and($movement->quantity)->toBe(20);
});

it('can record partial and full payments on purchase orders from view page', function () {
    $this->actingAs($this->admin);

    // Initial state: totalAmount: 60 000, paidAmount: 20 000 => remaining: 40 000
    expect($this->purchaseOrder->remaining_amount)->toBe(40000.0)
        ->and($this->purchaseOrder->isPartiallyPaid())->toBeTrue()
        ->and($this->purchaseOrder->payment_percentage)->toBe(33);

    $component = Livewire::test(ViewPurchaseOrder::class, [
        'record' => $this->purchaseOrder->getRouteKey(),
    ]);

    // 1. Partial payment: +15 000 FCFA
    $component->callAction('recordPayment', [
        'payment_mode' => 'partial',
        'amount' => 15000,
        'notes' => 'Acompte par virement bancaire',
    ]);

    $fresh = $this->purchaseOrder->fresh();
    expect((float) $fresh->paidAmount)->toBe(35000.0)
        ->and($fresh->remaining_amount)->toBe(25000.0)
        ->and($fresh->isPartiallyPaid())->toBeTrue()
        ->and($fresh->payment_percentage)->toBe(58);

    // 2. Full remaining payment: +25 000 FCFA
    $component->callAction('recordPayment', [
        'payment_mode' => 'full',
        'amount' => 25000,
        'notes' => 'Solde par chèque',
    ]);

    $freshFinal = $this->purchaseOrder->fresh();
    expect((float) $freshFinal->paidAmount)->toBe(60000.0)
        ->and($freshFinal->remaining_amount)->toBe(0.0)
        ->and($freshFinal->isFullyPaid())->toBeTrue()
        ->and($freshFinal->payment_percentage)->toBe(100);
});

it('can record payment from purchase orders table action', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListPurchaseOrders::class)
        ->callTableAction('recordPayment', $this->purchaseOrder, [
            'payment_mode' => 'partial',
            'amount' => 10000,
        ]);

    expect((float) $this->purchaseOrder->fresh()->paidAmount)->toBe(30000.0);
});
