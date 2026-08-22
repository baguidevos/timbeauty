<?php

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'active' => true,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('can find clients by first name or last name via global search', function () {
    $this->actingAs($this->admin);

    $client = Client::create([
        'firstName' => 'Kodjo',
        'lastName' => 'Agbéyomé',
        'phone' => '+22890112233',
        'email' => 'kodjo@test.com',
        'totalVisits' => 5,
        'isLoyal' => true,
    ]);

    $results = ClientResource::getGlobalSearchResults('Kodjo');

    expect($results)->not->toBeEmpty()
        ->and($results->first()->title)->toBe('Kodjo Agbéyomé')
        ->and($results->first()->details)->toHaveKey('Téléphone', '+22890112233')
        ->and($results->first()->details)->toHaveKey('Visites', '5 visite(s)')
        ->and($results->first()->details)->toHaveKey('Fidélité', '⭐ Client Fidèle');
});

it('can find clients by phone number via global search', function () {
    $this->actingAs($this->admin);

    $client = Client::create([
        'firstName' => 'Afi',
        'lastName' => 'Mensah',
        'phone' => '+22892998877',
        'email' => 'afi@test.com',
    ]);

    $results = ClientResource::getGlobalSearchResults('92998877');

    expect($results)->not->toBeEmpty()
        ->and($results->first()->title)->toBe('Afi Mensah');
});

it('can find clients by email via global search', function () {
    $this->actingAs($this->admin);

    $client = Client::create([
        'firstName' => 'Koffi',
        'lastName' => 'Dodzi',
        'phone' => '+22891223344',
        'email' => 'dodzi.unique@domain.com',
    ]);

    $results = ClientResource::getGlobalSearchResults('dodzi.unique');

    expect($results)->not->toBeEmpty()
        ->and($results->first()->title)->toBe('Koffi Dodzi');
});

it('generates correct view url for global search result', function () {
    $this->actingAs($this->admin);

    $client = Client::create([
        'firstName' => 'Akou',
        'lastName' => 'Lawson',
        'phone' => '+22890554433',
    ]);

    $results = ClientResource::getGlobalSearchResults('Lawson');
    $resultItem = $results->first();

    expect($resultItem->url)->toContain((string) $client->id);
});
