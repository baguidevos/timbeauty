<?php

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use App\Models\CashRegister;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin-middleware@test.com',
        'active' => true,
    ]);
});

it('allows access to dashboard when no unclosed past cash register exists', function () {
    $this->actingAs($this->admin);

    // No cash register or cash register opened today
    CashRegister::create([
        'openingAmount' => 10000,
        'status' => 'open',
        'openedAt' => now(),
        'openedBy' => $this->admin->id,
    ]);

    $response = $this->get('/admin');
    $response->assertSuccessful();
});

it('redirects to the unclosed past cash register view page when an open register from yesterday exists', function () {
    $this->actingAs($this->admin);

    // Create an open cash register from yesterday
    $yesterdayRegister = CashRegister::create([
        'openingAmount' => 15000,
        'status' => 'open',
        'openedAt' => now()->subDay()->setTime(8, 0),
        'openedBy' => $this->admin->id,
    ]);

    $targetUrl = CashRegisterResource::getUrl('view', ['record' => $yesterdayRegister]);

    // Attempting to access admin dashboard
    $response = $this->get('/admin');
    $response->assertRedirect($targetUrl);

    // Attempting to access products index
    $responseProducts = $this->get('/admin/catalog/products');
    $responseProducts->assertRedirect($targetUrl);
});

it('allows access to the unclosed cash register view page itself so it can be closed', function () {
    $this->actingAs($this->admin);

    $yesterdayRegister = CashRegister::create([
        'openingAmount' => 15000,
        'status' => 'open',
        'openedAt' => now()->subDay()->setTime(8, 0),
        'openedBy' => $this->admin->id,
    ]);

    $targetUrl = CashRegisterResource::getUrl('view', ['record' => $yesterdayRegister]);

    $response = $this->get($targetUrl);
    $response->assertSuccessful();
});

it('allows access to other pages once the previous cash register is closed', function () {
    $this->actingAs($this->admin);

    $yesterdayRegister = CashRegister::create([
        'openingAmount' => 15000,
        'closingAmount' => 15000,
        'status' => 'closed',
        'openedAt' => now()->subDay()->setTime(8, 0),
        'closedAt' => now()->subDay()->setTime(20, 0),
        'openedBy' => $this->admin->id,
    ]);

    $response = $this->get('/admin');
    $response->assertSuccessful();
});
