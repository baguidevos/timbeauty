<?php

use App\Filament\Resources\Barbers\Pages\ViewBarber;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Client;
use App\Models\RevenueTarget;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StaffAttendance;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin-view-barber@test.com',
        'active' => true,
    ]);

    $this->category = ServiceCategory::create([
        'name' => 'Prestations Coiffure',
        'order' => 1,
    ]);

    $this->service = Service::create([
        'name' => 'Coupe Tendance VIP',
        'price' => 5000,
        'duration' => 45,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Alex',
        'lastName' => 'Tchala',
        'phone' => '+228 92 33 44 55',
        'jobTitle' => 'barber',
        'status' => 'active',
        'canPerformServices' => true,
        'remunerationType' => 'commission',
        'commissionRate' => 20,
        'hireDate' => now()->subMonths(6)->toDateString(),
    ]);

    $this->client = Client::create([
        'firstName' => 'Kokou',
        'lastName' => 'Mensah',
        'phone' => '+228 90 99 88 77',
    ]);

    Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => now()->toDateString(),
        'startTime' => '10:00',
        'endTime' => '10:45',
        'status' => 'completed',
    ]);

    Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'subtotal' => 5000,
        'discountAmount' => 0,
        'total' => 5000,
        'paymentMethod' => 'cash',
        'status' => 'completed',
        'createdBy' => $this->admin->id,
    ]);

    StaffAttendance::create([
        'barberId' => $this->barber->id,
        'date' => now()->toDateString(),
        'clockIn' => now()->setTime(8, 15),
        'workedMinutes' => 480,
        'status' => 'present',
    ]);

    RevenueTarget::create([
        'type' => 'barber',
        'barberId' => $this->barber->id,
        'month' => (int) now()->format('m'),
        'year' => (int) now()->format('Y'),
        'targetAmount' => 100000,
        'createdBy' => $this->admin->id,
    ]);
});

it('can render enhanced staff profile view with KPIs, attendance and relations', function () {
    $this->actingAs($this->admin);

    Livewire::test(ViewBarber::class, [
        'record' => $this->barber->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Alex Tchala')
        ->assertSee('Coiffeur / Barbier')
        ->assertSee('5 000 FCFA')
        ->assertSee('Commission (20%)')
        ->assertSee('Présent')
        ->assertSee('Coupe Tendance VIP')
        ->assertSee('Kokou Mensah');
});
