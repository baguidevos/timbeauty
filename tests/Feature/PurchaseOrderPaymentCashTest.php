<?php

use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\OwnerAdvance;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Purchase',
        'email' => 'admin-purchase-cash@test.com',
        'active' => true,
    ]);

    $this->supplier = Supplier::create([
        'name' => 'Grossiste Beauté SARL',
        'phone' => '+228 91 22 33 44',
        'email' => 'grossiste@test.com',
        'address' => 'Lomé, Togo',
    ]);

    $this->category = ProductCategory::create([
        'name' => 'Produits Soins',
    ]);

    $this->expenseCategory = ExpenseCategory::create([
        'name' => 'Achat Produits & Consommables',
        'icon' => 'heroicon-o-shopping-bag',
    ]);

    $this->product = Product::create([
        'name' => 'Shampoing Kératine 1L',
        'sellingPrice' => 10000,
        'purchasePrice' => 5000,
        'stockQuantity' => 10,
        'minStockLevel' => 2,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->order = PurchaseOrder::create([
        'reference' => 'CMD-CASH-001',
        'supplierId' => $this->supplier->id,
        'orderDate' => now()->toDateString(),
        'status' => 'pending',
        'totalAmount' => 50000,
        'paidAmount' => 0,
        'createdBy' => $this->admin->id,
    ]);

    $this->item = PurchaseOrderItem::create([
        'purchaseOrderId' => $this->order->id,
        'productId' => $this->product->id,
        'productName' => $this->product->name,
        'quantity' => 10,
        'unitPrice' => 5000,
        'totalPrice' => 50000,
        'receivedQuantity' => 0,
    ]);

    $this->cashRegister = CashRegister::create([
        'openingAmount' => 100000,
        'status' => 'open',
        'openedAt' => now(),
        'openedBy' => $this->admin->id,
    ]);
});

it('can record a purchase order payment without cash deduction', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('recordPayment', [
            'payment_mode' => 'partial',
            'amount' => 20000,
            'payment_notes' => 'Virement bancaire direct',
            'deduct_from_cash' => false,
        ])
        ->assertHasNoActionErrors();

    $this->order->refresh();
    expect((float) $this->order->paidAmount)->toBe(20000.0)
        ->and($this->order->payment_status)->toBe('partially_paid')
        ->and($this->order->remaining_amount)->toBe(30000.0);

    // No expense or cash transaction created
    expect(Expense::count())->toBe(0)
        ->and(CashTransaction::count())->toBe(0)
        ->and((float) $this->cashRegister->getTheoreticalBalance())->toBe(100000.0);
});

it('deducts payment from cash register when sufficient balance exists', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('recordPayment', [
            'payment_mode' => 'full',
            'amount' => 50000,
            'payment_notes' => 'Paiement espèces au livreur',
            'deduct_from_cash' => true,
            'cash_register_id' => $this->cashRegister->id,
        ])
        ->assertHasNoActionErrors();

    $this->order->refresh();
    expect((float) $this->order->paidAmount)->toBe(50000.0)
        ->and($this->order->payment_status)->toBe('paid')
        ->and($this->order->isFullyPaid())->toBeTrue();

    // Expense created
    $expense = Expense::first();
    expect($expense)->not->toBeNull()
        ->and((float) $expense->amount)->toBe(50000.0)
        ->and($expense->paymentMethod)->toBe('cash')
        ->and($expense->beneficiary)->toBe($this->supplier->name)
        ->and($expense->cashRegisterId)->toBe($this->cashRegister->id);

    // Cash transaction created and balance updated
    expect(CashTransaction::where('type', 'expense')->count())->toBe(1)
        ->and((float) $this->cashRegister->getTheoreticalBalance())->toBe(50000.0); // 100000 - 50000
});

it('creates owner advance and deducts payment when cash balance is insufficient', function () {
    $this->actingAs($this->admin);

    // Set low balance on cash register: opening 15000
    $this->cashRegister->update(['openingAmount' => 15000]);
    expect((float) $this->cashRegister->getTheoreticalBalance())->toBe(15000.0);

    // Order is 50000 => deficit of 35000
    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('recordPayment', [
            'payment_mode' => 'full',
            'amount' => 50000,
            'payment_notes' => 'Espèces réglées après apport patron',
            'deduct_from_cash' => true,
            'cash_register_id' => $this->cashRegister->id,
            'owner_advance_enabled' => true,
            'owner_advance_user_id' => $this->admin->id,
            'owner_advance_amount' => 35000,
            'owner_advance_reason' => 'Apport pour règlement commande CMD-CASH-001',
        ])
        ->assertHasNoActionErrors();

    $this->order->refresh();
    expect((float) $this->order->paidAmount)->toBe(50000.0)
        ->and($this->order->isFullyPaid())->toBeTrue();

    // Owner advance created
    $advance = OwnerAdvance::first();
    expect($advance)->not->toBeNull()
        ->and((float) $advance->amount)->toBe(35000.0)
        ->and($advance->userId)->toBe($this->admin->id)
        ->and($advance->cashRegisterId)->toBe($this->cashRegister->id)
        ->and($advance->status)->toBe('pending');

    // Owner contribution transaction created
    expect(CashTransaction::where('type', 'owner_contribution')->count())->toBe(1);

    // Expense created
    expect(Expense::count())->toBe(1);

    // Theoretical balance: 15000 (opening) + 35000 (advance) - 50000 (expense) = 0
    expect((float) $this->cashRegister->getTheoreticalBalance())->toBe(0.0);
});

it('can deduct payment during markAsOrdered flow with toggle', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewPurchaseOrder::class, ['record' => $this->order->getRouteKey()])
        ->callAction('markAsOrdered', [
            'record_payment' => true,
            'payment_mode' => 'partial',
            'amount' => 10000,
            'deduct_from_cash' => true,
            'cash_register_id' => $this->cashRegister->id,
        ])
        ->assertHasNoActionErrors();

    $this->order->refresh();
    expect($this->order->status)->toBe('ordered')
        ->and((float) $this->order->paidAmount)->toBe(10000.0);

    expect((float) $this->cashRegister->getTheoreticalBalance())->toBe(90000.0);
});
