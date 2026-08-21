<?php

use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin-view-client@test.com',
        'active' => true,
    ]);

    $this->category = ServiceCategory::create([
        'name' => 'Coiffure Homme',
        'order' => 1,
    ]);

    $this->service = Service::create([
        'name' => 'Coupe & Dégradé',
        'price' => 4000,
        'duration' => 30,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Marc',
        'lastName' => 'Koffi',
        'phone' => '+228 90 11 22 33',
        'status' => 'active',
        'canPerformServices' => true,
    ]);

    $this->goldTier = LoyaltyTier::create([
        'name' => 'Gold VIP',
        'minPoints' => 500,
        'pointsPerFCFA' => 0.001,
        'discountPercentage' => 10,
        'color' => '#f59e0b',
        'status' => 'active',
    ]);

    $this->client = Client::create([
        'firstName' => 'Jean-Luc',
        'lastName' => 'Ajavon',
        'phone' => '+228 91 23 45 67',
        'whatsapp' => '+228 91 23 45 67',
        'email' => 'jeanluc@test.com',
        'gender' => 'male',
        'isLoyal' => true,
        'loyaltyPoints' => 350,
        'totalVisits' => 8,
        'totalSpent' => 42000,
        'notes' => 'Préfère le thé vert sans sucre et dégradé à blanc.',
        'firstVisitDate' => now()->subMonths(3)->toDateString(),
        'lastVisitDate' => now()->subDays(5)->toDateString(),
    ]);

    Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => now()->subDays(5)->toDateString(),
        'startTime' => '14:00',
        'endTime' => '14:30',
        'status' => 'completed',
    ]);

    Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'subtotal' => 4000,
        'discountAmount' => 0,
        'total' => 4000,
        'paymentMethod' => 'cash',
        'status' => 'completed',
        'createdBy' => $this->admin->id,
    ]);
});

it('can render enhanced client profile view with stats and relations', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewClient::class, [
        'record' => $this->client->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Jean-Luc Ajavon')
        ->assertSee('42 000 FCFA')
        ->assertSee('8 visites')
        ->assertSee('350')
        ->assertSee('Marc Koffi')
        ->assertSee('Coupe & Dégradé')
        ->assertSee('Préfère le thé vert sans sucre');
});

it('can update client notes directly from view page', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewClient::class, [
        'record' => $this->client->getRouteKey(),
    ])
        ->set('clientNote', 'Nouvelle note mise à jour en direct')
        ->call('saveNotes');

    expect($this->client->fresh()->notes)->toBe('Nouvelle note mise à jour en direct');
});
