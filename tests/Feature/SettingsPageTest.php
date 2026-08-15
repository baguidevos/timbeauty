<?php

use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'active' => true,
    ]);
});

it('can render settings page for authenticated user', function () {
    $this->actingAs($this->admin);

    Livewire::test(Settings::class)
        ->assertSuccessful()
        ->assertSee('Salon')
        ->assertSee('Caisse')
        ->assertSee('Fidélité')
        ->assertSee('Tickets');
});

it('can save shop settings', function () {
    $this->actingAs($this->admin);

    Livewire::test(Settings::class)
        ->set('shop_name', 'Salon Elite')
        ->set('shop_email', 'contact@elite.com')
        ->set('shop_phone', '+228 99 99 99 99')
        ->set('shop_address', 'Avenue de la Paix')
        ->set('currency', 'FCFA')
        ->call('saveShop')
        ->assertHasNoErrors();

    expect(Setting::get('shop_name'))->toBe('Salon Elite')
        ->and(Setting::get('shop_email'))->toBe('contact@elite.com')
        ->and(Setting::get('shop_phone'))->toBe('+228 99 99 99 99')
        ->and(Setting::get('shop_address'))->toBe('Avenue de la Paix')
        ->and(Setting::get('currency'))->toBe('FCFA');
});

it('can save cash settings and toggle payment methods', function () {
    $this->actingAs($this->admin);

    Livewire::test(Settings::class)
        ->set('cash_register_mode', 'strict')
        ->call('togglePaymentMethod', 'crypto')
        ->call('saveCash')
        ->assertHasNoErrors();

    expect(Setting::get('cash_register_mode'))->toBe('strict')
        ->and(Setting::get('payment_methods_enabled'))->toContain('crypto');
});

it('can save loyalty settings', function () {
    $this->actingAs($this->admin);

    Livewire::test(Settings::class)
        ->set('loyalty_visits_threshold', '10')
        ->set('loyalty_auto_notify', true)
        ->call('saveLoyalty')
        ->assertHasNoErrors();

    expect(Setting::get('loyalty_visits_threshold'))->toBe('10')
        ->and(Setting::get('loyalty_auto_notify'))->toBe('1');
});

it('can save receipt settings', function () {
    $this->actingAs($this->admin);

    Livewire::test(Settings::class)
        ->set('receipt_header', "Salon Test\nLomé")
        ->set('receipt_footer', 'Merci !')
        ->set('receipt_printer_width', '80')
        ->call('saveReceipt')
        ->assertHasNoErrors();

    expect(Setting::get('receipt_header'))->toBe("Salon Test\nLomé")
        ->and(Setting::get('receipt_footer'))->toBe('Merci !')
        ->and(Setting::get('receipt_printer_width'))->toBe('80');
});

it('can toggle user active status from settings page', function () {
    $this->actingAs($this->admin);

    $targetUser = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@test.com',
        'active' => true,
    ]);

    Livewire::test(Settings::class)
        ->call('toggleUserActive', $targetUser->id)
        ->assertHasNoErrors();

    expect($targetUser->fresh()->active)->toBeFalse();
});
