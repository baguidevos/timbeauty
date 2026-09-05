<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('roles and permissions seeder initializes expected core roles', function () {
    expect(Role::where('name', 'super_admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'cashier')->exists())->toBeTrue()
        ->and(Role::where('name', 'barber')->exists())->toBeTrue();
});

test('user can be assigned roles and checked via hasRole', function () {
    $user = User::factory()->create([
        'name' => 'Jean Dupont',
        'email' => 'jean@test.com',
        'active' => true,
    ]);

    $user->assignRole('cashier');

    expect($user->hasRole('cashier'))->toBeTrue()
        ->and($user->isCashier())->toBeTrue()
        ->and($user->isAdmin())->toBeFalse();
});

test('super admin bypasses all authorization gates', function () {
    $superAdmin = User::factory()->create([
        'name' => 'Super User',
        'email' => 'super@test.com',
        'active' => true,
    ]);
    $superAdmin->assignRole('super_admin');

    // Super admin role allows any ability via define_via_gate
    expect(Gate::forUser($superAdmin)->allows('any_random_ability'))->toBeTrue()
        ->and(Gate::forUser($superAdmin)->allows('View:CashRegister'))->toBeTrue()
        ->and($superAdmin->isAdmin())->toBeTrue();
});

test('cashier role has access to sales and cash registers but not arbitrary abilities', function () {
    $cashier = User::factory()->create([
        'name' => 'Alice Caissière',
        'email' => 'alice@test.com',
        'active' => true,
    ]);
    $cashier->assignRole('cashier');

    // Cashier does not have random abilities
    expect(Gate::forUser($cashier)->allows('manage_system_settings_xyz'))->toBeFalse()
        ->and($cashier->isAdmin())->toBeFalse()
        ->and($cashier->isCashier())->toBeTrue();
});

test('inactive user is denied access to filament panel', function () {
    $user = User::factory()->create([
        'active' => false,
    ]);

    $panel = Filament\Facades\Filament::getCurrentOrDefaultPanel();

    expect($user->canAccessPanel($panel))->toBeFalse();
});
