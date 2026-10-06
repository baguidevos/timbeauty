<?php

use App\Events\ClientCreated;
use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;

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

it('automatically generates a client code with 2 first letters, 4 last digits, hyphen and numeric id on creation', function () {
    Event::fake([ClientCreated::class]);

    $client = Client::create([
        'firstName' => 'Jean-Luc',
        'lastName' => 'Ajavon',
        'phone' => '+228 91 23 45 67',
    ]);

    expect($client->code)->toBe("JE4567-{$client->id}")
        ->and($client->clientCode)->toBe("JE4567-{$client->id}");

    Event::assertDispatched(ClientCreated::class, function ($event) use ($client) {
        return $event->client->id === $client->id;
    });
});

it('can find clients by their generated client code via global search', function () {
    $this->actingAs($this->admin);

    $client = Client::create([
        'firstName' => 'Pauline',
        'lastName' => 'Koffi',
        'phone' => '+228 90 99 88 11',
        'email' => 'pauline@test.com',
    ]);

    expect($client->code)->toBe("PA8811-{$client->id}");

    // Search by exact code
    $results = ClientResource::getGlobalSearchResults($client->code);

    expect($results)->not->toBeEmpty()
        ->and($results->first()->title)->toBe('Pauline Koffi')
        ->and($results->first()->details)->toHaveKey('Code', $client->code);

    // Search by lower case code
    $lowerCode = strtolower($client->code);
    $resultsLower = ClientResource::getGlobalSearchResults($lowerCode);

    expect($resultsLower)->not->toBeEmpty()
        ->and($resultsLower->first()->title)->toBe('Pauline Koffi');
});

it('correctly handles accented characters and edge cases in client code generation', function () {
    $client1 = Client::create([
        'firstName' => 'Sénam',
        'lastName' => 'Klouvi',
        'phone' => '+228 96 77 88 99',
    ]);
    expect($client1->code)->toBe("SE8899-{$client1->id}");

    $client2 = Client::create([
        'firstName' => 'A',
        'lastName' => 'Short',
        'phone' => '12',
    ]);
    expect($client2->code)->toBe("AX0012-{$client2->id}");

    $client3 = Client::create([
        'firstName' => 'Noname',
        'lastName' => 'Nophone',
        'phone' => '',
        'whatsapp' => '',
    ]);
    expect($client3->code)->toBe("NO0000-{$client3->id}");
});
