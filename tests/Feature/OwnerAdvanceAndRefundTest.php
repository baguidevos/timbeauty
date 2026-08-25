<?php

use App\Filament\Resources\CashRegisters\Pages\ViewCashRegister;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\OwnerAdvance;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->owner = User::factory()->create([
        'name' => 'Propriétaire Salon',
        'email' => 'proprio@salon.com',
        'active' => true,
    ]);

    $this->cashRegister = CashRegister::create([
        'openingAmount' => 20000,
        'status' => 'open',
        'openedAt' => now(),
        'openedBy' => $this->owner->id,
    ]);
});

test('owner advance increases theoretical balance and records cash transaction', function () {
    $this->actingAs($this->owner);

    Livewire::test(ViewCashRegister::class, ['record' => $this->cashRegister->id])
        ->callAction('ownerAdvance', [
            'userId' => $this->owner->id,
            'amount' => 50000,
            'reason' => 'Apport urgent pour paiement salaires',
            'notes' => 'Fonds personnels',
        ])
        ->assertHasNoActionErrors();

    // Vérifier l'avance
    $advance = OwnerAdvance::first();
    expect($advance)->not->toBeNull()
        ->and($advance->amount)->toEqual(50000)
        ->and($advance->refundedAmount)->toEqual(0)
        ->and($advance->remaining_amount)->toEqual(50000)
        ->and($advance->status)->toBe('pending');

    // Vérifier la transaction de caisse
    $transaction = CashTransaction::where('type', 'owner_contribution')->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->amount)->toEqual(50000)
        ->and($transaction->cashRegisterId)->toBe($this->cashRegister->id);

    // Vérifier le solde théorique de caisse (20 000 + 50 000 = 70 000)
    expect($this->cashRegister->fresh()->getTheoreticalBalance())->toEqual(70000);
});

test('owner refund reduces theoretical balance and updates advance status', function () {
    $this->actingAs($this->owner);

    // Créer une avance de 50 000
    $advance = OwnerAdvance::create([
        'cashRegisterId' => $this->cashRegister->id,
        'userId' => $this->owner->id,
        'amount' => 50000,
        'reason' => 'Avance salaires',
        'createdBy' => $this->owner->id,
    ]);

    CashTransaction::create([
        'cashRegisterId' => $this->cashRegister->id,
        'type' => 'owner_contribution',
        'amount' => 50000,
        'description' => 'Avance propriétaire',
        'createdBy' => $this->owner->id,
    ]);

    // Solde théorique = 70 000. Rembourser 30 000
    Livewire::test(ViewCashRegister::class, ['record' => $this->cashRegister->id])
        ->callAction('ownerRefund', [
            'ownerAdvanceId' => $advance->id,
            'amount' => 30000,
            'notes' => 'Premier remboursement partiel',
        ])
        ->assertHasNoActionErrors();

    $advance->refresh();
    expect($advance->refundedAmount)->toEqual(30000)
        ->and($advance->remaining_amount)->toEqual(20000)
        ->and($advance->status)->toBe('partially_refunded');

    // Solde théorique après remboursement partiel : 70 000 - 30 000 = 40 000
    expect($this->cashRegister->fresh()->getTheoreticalBalance())->toEqual(40000);

    // Deuxième remboursement pour solder (20 000)
    Livewire::test(ViewCashRegister::class, ['record' => $this->cashRegister->id])
        ->callAction('ownerRefund', [
            'ownerAdvanceId' => $advance->id,
            'amount' => 20000,
            'notes' => 'Solde final',
        ])
        ->assertHasNoActionErrors();

    $advance->refresh();
    expect($advance->refundedAmount)->toEqual(50000)
        ->and($advance->remaining_amount)->toEqual(0)
        ->and($advance->status)->toBe('refunded');

    // Solde théorique final : 40 000 - 20 000 = 20 000
    expect($this->cashRegister->fresh()->getTheoreticalBalance())->toEqual(20000);
});
