<?php

use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Filament\Widgets\KpiOverviewWidget;
use App\Models\Barber;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Sales',
        'email' => 'admin-sales@test.com',
        'active' => true,
    ]);

    $this->client = Client::create([
        'firstName' => 'Amivi',
        'lastName' => 'Koffi',
        'phone' => '+228 91 22 33 44',
        'firstVisitDate' => now()->toDateString(),
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Barber',
        'lastName' => 'Max',
        'phone' => '+228 90 11 22 33',
        'status' => 'active',
        'canPerformServices' => true,
    ]);

    $this->saleWithoutDiscount = Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'subtotal' => 5000,
        'discountAmount' => 0,
        'total' => 5000,
        'paymentMethod' => 'cash',
        'status' => 'completed',
        'createdBy' => $this->admin->id,
    ]);

    $this->saleWithDiscount = Sale::create([
        'clientId' => $this->client->id,
        'barberId' => $this->barber->id,
        'subtotal' => 10000,
        'discountAmount' => 2000,
        'total' => 8000,
        'paymentMethod' => 'tmoney',
        'status' => 'completed',
        'createdBy' => $this->admin->id,
    ]);
});

it('can render sales list and filter by has_discount', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListSales::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->saleWithoutDiscount, $this->saleWithDiscount])
        ->filterTable('has_discount', true)
        ->assertCanSeeTableRecords([$this->saleWithDiscount])
        ->assertCanNotSeeTableRecords([$this->saleWithoutDiscount]);
});

it('can render edit client page with relations', function () {
    $this->actingAs($this->admin);

    Livewire::test(EditClient::class, [
        'record' => $this->client->getRouteKey(),
    ])
        ->assertSuccessful();
});

it('calculates discount totals correctly in KPI overview widget', function () {
    $this->actingAs($this->admin);

    Livewire::test(KpiOverviewWidget::class)
        ->assertSuccessful()
        ->assertSet('todayDiscountTotal', 2000.0)
        ->assertSet('monthDiscountTotal', 2000.0);
});
