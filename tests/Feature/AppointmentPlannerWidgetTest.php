<?php

use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\Widgets\AppointmentCalendarWidget;
use App\Filament\Resources\Appointments\Widgets\AppointmentPlannerWidget;
use App\Filament\Widgets\NextAppointmentWidget;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin-planner@test.com',
        'active' => true,
    ]);

    $this->category = ServiceCategory::create([
        'name' => 'Coiffure',
        'order' => 1,
    ]);

    $this->service = Service::create([
        'name' => 'Coupe & Barbe',
        'price' => 5000,
        'duration' => 45,
        'categoryId' => $this->category->id,
        'status' => 'active',
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Kodjo',
        'lastName' => 'Barber',
        'phone' => '+228 90 12 34 56',
        'status' => 'active',
        'canPerformServices' => true,
    ]);

    $this->client = Client::create([
        'firstName' => 'Afi',
        'lastName' => 'Client',
        'phone' => '+228 91 23 45 67',
        'firstVisitDate' => now()->toDateString(),
    ]);
});

it('can render appointment planner widget with today appointments', function () {
    $this->actingAs($this->admin);

    $today = Carbon::today()->toDateString();

    Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => $today,
        'startTime' => '10:00',
        'endTime' => '10:45',
        'status' => 'pending',
    ]);

    Livewire::test(AppointmentPlannerWidget::class)
        ->assertSuccessful()
        ->assertSee('Afi Client')
        ->assertSee('Kodjo Barber')
        ->assertSet('todayCount', 1)
        ->assertSet('pendingCount', 1);
});

it('can navigate between dates and view modes', function () {
    $this->actingAs($this->admin);

    $today = Carbon::today();
    $yesterday = $today->copy()->subDay()->toDateString();

    Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => $yesterday,
        'startTime' => '14:00',
        'endTime' => '14:45',
        'status' => 'confirmed',
    ]);

    $test = Livewire::test(AppointmentPlannerWidget::class)
        ->assertSuccessful()
        ->call('goPrev')
        ->assertSet('currentDate', $yesterday)
        ->assertSee('Afi Client');

    $test->call('setViewMode', 'week')
        ->assertSet('viewMode', 'week')
        ->assertSee('Afi Client');
});

it('can update appointment status from widget', function () {
    $this->actingAs($this->admin);

    $today = Carbon::today()->toDateString();

    $appointment = Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => $today,
        'startTime' => '11:00',
        'endTime' => '11:45',
        'status' => 'pending',
    ]);

    Livewire::test(AppointmentPlannerWidget::class)
        ->call('updateStatus', $appointment->id, 'confirmed')
        ->assertDispatched('filament-tables::refresh')
        ->assertDispatched('refreshAppointmentPlanner');

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('can render ListAppointments page with planner and table tabs', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListAppointments::class)
        ->assertSuccessful()
        ->assertSet('viewTab', 'planner')
        ->assertSee('Planning')
        ->assertSee('Tableau')
        ->set('viewTab', 'table')
        ->assertSet('viewTab', 'table')
        ->assertSee('Total Rendez-vous');
});

it('supports Eventable and Resourceable models for calendar', function () {
    $today = Carbon::today()->toDateString();

    $appointment = Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => $today,
        'startTime' => '10:00',
        'endTime' => '10:45',
        'status' => 'confirmed',
    ]);

    $calendarEvent = $appointment->toCalendarEvent();
    expect($calendarEvent->getTitle())->toContain('Afi Client')
        ->and($calendarEvent->getResourceIds())->toBe([(string) $this->barber->id]);

    $calendarResource = $this->barber->toCalendarResource();
    expect($calendarResource->getTitle())->toBe('Kodjo Barber')
        ->and($calendarResource->getId())->toBe((string) $this->barber->id);
});

it('can render appointment calendar widget', function () {
    $this->actingAs($this->admin);

    Livewire::test(AppointmentCalendarWidget::class)
        ->assertSuccessful();
});

it('renders next appointment widget with upcoming or pending appointment', function () {
    $this->actingAs($this->admin);

    $today = Carbon::today()->toDateString();

    Appointment::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'serviceId' => $this->service->id,
        'date' => $today,
        'startTime' => '09:00',
        'endTime' => '09:45',
        'status' => 'pending',
    ]);

    Livewire::test(NextAppointmentWidget::class)
        ->assertSuccessful()
        ->assertSee('Afi Client')
        ->assertSee('Coupe & Barbe')
        ->assertSee('Kodjo Barber');
});
