<?php

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'active' => true,
    ]);
});

it('automatically logs creation of a client', function () {
    $this->actingAs($this->admin);

    $client = Client::create([
        'firstName' => 'Jean',
        'lastName' => 'Dupont',
        'phone' => '+22890000001',
    ]);

    $log = ActivityLog::where('entity', 'Client')
        ->where('entityId', $client->id)
        ->where('action', 'create')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->userId)->toBe($this->admin->id)
        ->and($log->details)->toContain('Jean Dupont');
});

it('automatically logs updates to a model', function () {
    $this->actingAs($this->admin);

    $service = Service::create([
        'name' => 'Coupe Classique',
        'price' => 3000,
        'duration' => 30,
    ]);

    $service->update([
        'price' => 3500,
    ]);

    $log = ActivityLog::where('entity', 'Service')
        ->where('entityId', $service->id)
        ->where('action', 'update')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->userId)->toBe($this->admin->id)
        ->and($log->details)->toContain('Coupe Classique');
});

it('automatically logs deletion of a model', function () {
    $this->actingAs($this->admin);

    $product = Product::create([
        'name' => 'Gel Coiffant',
        'purchasePrice' => 2000,
        'sellingPrice' => 3500,
        'stockQuantity' => 10,
    ]);

    $productId = $product->id;
    $product->delete();

    $log = ActivityLog::where('entity', 'Product')
        ->where('entityId', $productId)
        ->where('action', 'delete')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->userId)->toBe($this->admin->id)
        ->and($log->details)->toContain('Gel Coiffant');
});

it('can suppress activity logging with withoutActivityLogging', function () {
    $this->actingAs($this->admin);

    $expense = Expense::withoutActivityLogging(function () {
        return Expense::create([
            'amount' => 5000,
            'date' => now()->toDateString(),
            'description' => 'Test dépense sans log',
        ]);
    });

    $log = ActivityLog::where('entity', 'Expense')
        ->where('entityId', $expense->id)
        ->first();

    expect($log)->toBeNull();
});

it('can render activity logs filament list page', function () {
    $this->actingAs($this->admin);

    ActivityLog::log(
        action: 'create',
        entity: 'Client',
        entityId: 1,
        details: 'Nouveau client enregistré : Paul Koffi',
        userId: $this->admin->id,
    );

    Livewire::test(ListActivityLogs::class)
        ->assertSuccessful()
        ->assertSee('Paul Koffi');
});
