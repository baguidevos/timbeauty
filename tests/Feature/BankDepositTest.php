<?php

use App\Filament\Resources\CashRegisters\Pages\ViewCashRegister;
use App\Models\BankDeposit;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Trésorerie',
        'email' => 'tresor@salon.com',
        'active' => true,
    ]);

    Setting::set('cash_bank_deposit_threshold', 100000);
    Setting::set('default_bank_name', 'Ecobank Togo');

    $this->cashRegister = CashRegister::create([
        'openingAmount' => 150000, // Supérieur au seuil de 100 000
        'status' => 'open',
        'openedAt' => now(),
        'openedBy' => $this->admin->id,
    ]);
});

test('bank deposit threshold is detected when balance exceeds setting', function () {
    expect($this->cashRegister->isBankDepositThresholdReached())->toBeTrue();

    // Si on change le seuil à 200 000, le seuil n'est plus atteint
    Setting::set('cash_bank_deposit_threshold', 200000);
    expect($this->cashRegister->isBankDepositThresholdReached())->toBeFalse();
});

test('bank deposit action records deposit and deducts cash from register', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewCashRegister::class, ['record' => $this->cashRegister->id])
        ->callAction('bankDeposit', [
            'amount' => 80000,
            'bankName' => 'Ecobank Togo',
            'bankAccountNumber' => 'TG001 12345 67890',
            'depositSlipNumber' => 'BORD-2026-001',
            'depositedBy' => $this->admin->id,
            'notes' => 'Écrémage fin de matinée',
        ])
        ->assertHasNoActionErrors();

    // Vérifier l'enregistrement BankDeposit
    $deposit = BankDeposit::first();
    expect($deposit)->not->toBeNull()
        ->and($deposit->amount)->toEqual(80000)
        ->and($deposit->bankName)->toBe('Ecobank Togo')
        ->and($deposit->depositSlipNumber)->toBe('BORD-2026-001')
        ->and($deposit->reference)->toStartWith('DEP-')
        ->and($deposit->status)->toBe('confirmed');

    // Vérifier la transaction de caisse
    $transaction = CashTransaction::where('type', 'bank_deposit')->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->amount)->toEqual(80000)
        ->and($transaction->cashRegisterId)->toBe($this->cashRegister->id);

    // Vérifier le solde théorique de caisse (150 000 - 80 000 = 70 000)
    expect($this->cashRegister->fresh()->getTheoreticalBalance())->toEqual(70000);
});

test('bank deposit cannot exceed current cash register balance', function () {
    $this->actingAs($this->admin);

    // Tenter de retirer 200 000 alors que la caisse ne contient que 150 000
    Livewire::test(ViewCashRegister::class, ['record' => $this->cashRegister->id])
        ->callAction('bankDeposit', [
            'amount' => 200000,
            'bankName' => 'Ecobank Togo',
            'depositSlipNumber' => 'BORD-TEST',
            'depositedBy' => $this->admin->id,
        ]);

    expect(BankDeposit::count())->toBe(0)
        ->and($this->cashRegister->fresh()->getTheoreticalBalance())->toEqual(150000);
});
