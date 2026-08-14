<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class BirthdayRemindersWidget extends Widget
{
    protected string $view = 'filament.widgets.birthday-reminders-widget';

    protected int|string|array $columnSpan = 'full';

    public array $birthdays = [];

    public int $thisMonthCount = 0;

    public function mount(): void
    {
        $this->loadBirthdays();
    }

    public function loadBirthdays(): void
    {
        $now = Carbon::now();
        $currentYear = $now->year;

        $clients = Client::whereNotNull('birthDate')->get();
        $upcoming = [];
        $monthCount = 0;

        foreach ($clients as $client) {
            $birthDate = Carbon::parse($client->birthDate);
            $nextBirthday = Carbon::createFromDate($currentYear, $birthDate->month, $birthDate->day);

            if ($nextBirthday->isPast() && ! $nextBirthday->isToday()) {
                $nextBirthday->addYear();
            }

            if ($birthDate->month === $now->month) {
                $monthCount++;
            }

            $daysUntil = (int) $now->startOfDay()->diffInDays($nextBirthday->startOfDay(), false);

            if ($daysUntil >= 0 && $daysUntil <= 14) {
                $age = $nextBirthday->year - $birthDate->year;
                $upcoming[] = [
                    'id' => $client->id,
                    'first_name' => $client->firstName,
                    'last_name' => $client->lastName,
                    'phone' => $client->phone,
                    'is_today' => $nextBirthday->isToday(),
                    'days_until' => $daysUntil,
                    'age' => $age,
                    'next_birthday_date' => $nextBirthday->format('d/m'),
                ];
            }
        }

        usort($upcoming, fn ($a, $b) => $a['days_until'] <=> $b['days_until']);

        $this->birthdays = array_slice($upcoming, 0, 5);
        $this->thisMonthCount = $monthCount;
    }

    public function generateReminders(): void
    {
        Notification::make()
            ->title('Rappels générés avec succès !')
            ->body(count($this->birthdays).' alerte(s) d\'anniversaires prêtes à être envoyées aux clients.')
            ->success()
            ->send();
    }
}
