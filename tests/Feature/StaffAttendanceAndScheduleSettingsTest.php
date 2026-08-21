<?php

use App\Filament\Resources\StaffAttendances\Widgets\StaffAttendanceWidget;
use App\Filament\Resources\StaffSchedules\Pages\ListStaffSchedules;
use App\Models\Barber;
use App\Models\Setting;
use App\Models\StaffAttendance;
use App\Models\StaffSchedule;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'active' => true,
    ]);

    $this->barber = Barber::create([
        'firstName' => 'Marc',
        'lastName' => 'Koffi',
        'phone' => '+228 90 00 11 22',
        'email' => 'marc@barbershop.com',
        'jobTitle' => 'barber',
        'status' => 'active',
        'canPerformServices' => true,
    ]);
});

it('uses default_opening_time setting to determine lateness on clock in', function () {
    $this->actingAs($this->admin);

    // Setting opening time to 08:30
    Setting::set('default_opening_time', '08:30');

    // Test arrival at 08:15 (on time)
    Carbon::setTestNow(Carbon::parse('2026-08-21 08:15:00'));

    Livewire::test(StaffAttendanceWidget::class)
        ->call('clockIn', $this->barber->id);

    $attendance = StaffAttendance::where('barberId', $this->barber->id)
        ->whereDate('date', '2026-08-21')
        ->first();

    expect($attendance)->not->toBeNull()
        ->and($attendance->status)->toBe('present');

    // Clean up
    $attendance->delete();

    // Test arrival at 08:45 (late relative to 08:30)
    Carbon::setTestNow(Carbon::parse('2026-08-21 08:45:00'));

    Livewire::test(StaffAttendanceWidget::class)
        ->call('clockIn', $this->barber->id);

    $attendanceLate = StaffAttendance::where('barberId', $this->barber->id)
        ->whereDate('date', '2026-08-21')
        ->first();

    expect($attendanceLate)->not->toBeNull()
        ->and($attendanceLate->status)->toBe('late');

    Carbon::setTestNow();
});

it('uses custom staff schedule start time over setting if defined when clocking in', function () {
    $this->actingAs($this->admin);

    Setting::set('default_opening_time', '08:00');

    // 2026-08-21 is Friday (dayOfWeek = 5)
    StaffSchedule::create([
        'barberId' => $this->barber->id,
        'dayOfWeek' => 5,
        'startTime' => '10:00',
        'endTime' => '18:00',
        'isDayOff' => false,
    ]);

    // Arrive at 09:30 (after salon open 08:00, but before staff schedule 10:00)
    Carbon::setTestNow(Carbon::parse('2026-08-21 09:30:00'));

    Livewire::test(StaffAttendanceWidget::class)
        ->call('clockIn', $this->barber->id);

    $attendance = StaffAttendance::where('barberId', $this->barber->id)
        ->whereDate('date', '2026-08-21')
        ->first();

    expect($attendance)->not->toBeNull()
        ->and($attendance->status)->toBe('present');

    Carbon::setTestNow();
});

it('uses settings default hours when configuring staff schedule matrix and quick set', function () {
    $this->actingAs($this->admin);

    Setting::set('default_opening_time', '07:30');
    Setting::set('default_closing_time', '21:00');

    $component = Livewire::test(ListStaffSchedules::class);

    // Quick set should assign the settings hours
    $component->call('quickSetHours', $this->barber->id, 2);

    $schedule = StaffSchedule::where('barberId', $this->barber->id)
        ->where('dayOfWeek', 2)
        ->first();

    expect($schedule)->not->toBeNull()
        ->and(substr((string) $schedule->startTime, 0, 5))->toBe('07:30')
        ->and(substr((string) $schedule->endTime, 0, 5))->toBe('21:00');

    // Matrix should now reflect the updated schedule
    $matrix = $component->get('scheduleMatrix');
    expect($matrix)->not->toBeEmpty();
    expect($matrix[0]['days'][2]['start_time'])->toBe('07:30')
        ->and($matrix[0]['days'][2]['end_time'])->toBe('21:00');
});
