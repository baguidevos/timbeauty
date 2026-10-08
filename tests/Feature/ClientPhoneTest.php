<?php

use App\Filament\Pages\Pos;
use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Models\Client;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test Client Phone',
        'email' => 'admin-client-phone@test.com',
        'active' => true,
    ]);
});

it('can create a client without phone number via model', function () {
    $client = Client::create([
        'firstName' => 'Ablan',
        'lastName' => 'Dovi',
        'phone' => null,
    ]);

    expect($client->id)->not->toBeNull()
        ->and($client->phone)->toBeNull()
        ->and($client->code)->not->toBeNull();
});

it('converts prefix-only phone number to null on client save', function () {
    $client = Client::create([
        'firstName' => 'Kodjo',
        'lastName' => 'Mensah',
        'phone' => '+228 ',
    ]);

    expect($client->phone)->toBeNull();

    $clientWithSpaceOnly = Client::create([
        'firstName' => 'Afi',
        'lastName' => 'Lawson',
        'phone' => '+228',
    ]);

    expect($clientWithSpaceOnly->phone)->toBeNull();
});

it('preserves valid phone number with prefix', function () {
    $client = Client::create([
        'firstName' => 'Foli',
        'lastName' => 'Amegan',
        'phone' => '+228 90 12 34 56',
    ]);

    expect($client->phone)->toBe('+228 90 12 34 56');
});

it('can create a client without phone number via Filament CreateClient page', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateClient::class)
        ->fillForm([
            'firstName' => 'Séwa',
            'lastName' => 'Kponton',
            'phone' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = Client::where('firstName', 'Séwa')->where('lastName', 'Kponton')->first();
    expect($client)->not->toBeNull()
        ->and($client->phone)->toBeNull();
});

it('can create a client with default +228 prefix left untouched via CreateClient page', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateClient::class)
        ->fillForm([
            'firstName' => 'Mawuéna',
            'lastName' => 'Akakpo',
            'phone' => '+228 ',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $client = Client::where('firstName', 'Mawuéna')->where('lastName', 'Akakpo')->first();
    expect($client)->not->toBeNull()
        ->and($client->phone)->toBeNull();
});

it('can create a quick client in POS without phone or with default prefix', function () {
    $this->actingAs($this->admin);

    Livewire::test(Pos::class)
        ->set('quickClientFirstName', 'Komla')
        ->set('quickClientLastName', 'Agbessi')
        ->set('quickClientPhone', '+228 ')
        ->call('createQuickClient')
        ->assertHasNoErrors()
        ->assertSet('showQuickClientModal', false);

    $client = Client::where('firstName', 'Komla')->where('lastName', 'Agbessi')->first();
    expect($client)->not->toBeNull()
        ->and($client->phone)->toBeNull();
});
