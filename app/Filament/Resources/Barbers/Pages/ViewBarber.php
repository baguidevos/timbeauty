<?php

namespace App\Filament\Resources\Barbers\Pages;

use App\Filament\Resources\Barbers\BarberResource;
use App\Filament\Resources\StaffSchedules\StaffScheduleResource;
use App\Models\Appointment;
use App\Models\RevenueTarget;
use App\Models\Sale;
use App\Models\Service;
use App\Models\StaffAttendance;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ViewBarber extends ViewRecord
{
    protected static string $resource = BarberResource::class;

    protected string $view = 'filament.resources.barbers.pages.view-barber';

    public string $activeTab = 'appointments';

    public function getTitle(): string
    {
        return "Fiche Collaborateur : {$this->record->getFullName()}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            BarberResource::getUrl('index') => 'Personnel',
            $this->record->getFullName(),
        ];
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        $barber = $this->record;

        // 1. WhatsApp Action
        if ($barber->phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $barber->phone);
            if (strlen($cleanPhone) === 8) {
                $cleanPhone = '228'.$cleanPhone;
            }
            $actions[] = Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->url("https://wa.me/{$cleanPhone}?text=".urlencode("Bonjour {$barber->firstName},"), true);
        }

        // 2. Planning
        $actions[] = Action::make('viewSchedule')
            ->label('Planning')
            ->icon('heroicon-o-calendar-days')
            ->color('info')
            ->url(StaffScheduleResource::getUrl('index'));

        // 3. Quick Clock-in/Clock-out
        $todayAttendance = StaffAttendance::where('barberId', $barber->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (! $todayAttendance) {
            $actions[] = Action::make('clockIn')
                ->label('Pointer Arrivée')
                ->icon('heroicon-m-arrow-right-end-on-rectangle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Pointer l\'arrivée')
                ->modalDescription("Enregistrer l'arrivée de {$barber->getFullName()} maintenant ?")
                ->action(function () use ($barber) {
                    $now = now();
                    StaffAttendance::create([
                        'barberId' => $barber->id,
                        'date' => $now->toDateString(),
                        'clockIn' => $now,
                        'status' => 'present',
                    ]);

                    Notification::make()
                        ->title("Arrivée pointée pour {$barber->getFullName()}")
                        ->success()
                        ->send();
                });
        } elseif (! $todayAttendance->clockOut) {
            $actions[] = Action::make('clockOut')
                ->label('Pointer Départ')
                ->icon('heroicon-m-arrow-left-start-on-rectangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Pointer le départ')
                ->modalDescription("Enregistrer le départ de {$barber->getFullName()} maintenant ?")
                ->action(function () use ($todayAttendance, $barber) {
                    $now = now();
                    $diffMinutes = max(0, Carbon::parse($todayAttendance->clockIn)->diffInMinutes($now));
                    $hours = round($diffMinutes / 60, 1);

                    $todayAttendance->update([
                        'clockOut' => $now,
                        'workedMinutes' => $diffMinutes,
                    ]);

                    Notification::make()
                        ->title("Départ pointé pour {$barber->getFullName()} ({$hours}h)")
                        ->success()
                        ->send();
                });
        }

        // 4. Edit
        $actions[] = EditAction::make()
            ->label('Modifier')
            ->color('gray');

        return $actions;
    }

    public function getBarberStats(): array
    {
        $barber = $this->record;
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        // Today's attendance status
        $todayAttendance = StaffAttendance::where('barberId', $barber->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $todayStatus = 'not_clocked';
        if ($todayAttendance) {
            if ($todayAttendance->clockOut) {
                $todayStatus = 'completed';
            } elseif ($todayAttendance->status === 'break') {
                $todayStatus = 'break';
            } else {
                $todayStatus = 'present';
            }
        } elseif ($barber->status === 'on_leave') {
            $todayStatus = 'on_leave';
        }

        // Monthly Revenue generated
        $monthRevenue = (float) Sale::where('barberId', $barber->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $totalRevenue = (float) Sale::where('barberId', $barber->id)
            ->where('status', 'completed')
            ->sum('total');

        // Services completed this month
        $monthAppointmentsCount = Appointment::where('barberId', $barber->id)
            ->where('status', 'completed')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();

        $totalAppointmentsCount = Appointment::where('barberId', $barber->id)
            ->where('status', 'completed')
            ->count();

        // Estimated commissions this month
        $estimatedCommission = 0;
        if ($barber->remunerationType === 'commission' || $barber->remunerationType === 'fixed_plus_commission') {
            $rate = (float) ($barber->commissionRate ?? 0);
            $estimatedCommission = round(($monthRevenue * $rate) / 100);
        } elseif ($barber->remunerationType === 'per_service') {
            $rate = (float) ($barber->perServiceRate ?? 0);
            $estimatedCommission = $monthAppointmentsCount * $rate;
        }

        // Monthly Revenue Target
        $target = RevenueTarget::where('barberId', $barber->id)
            ->where('month', (int) now()->format('m'))
            ->where('year', (int) now()->format('Y'))
            ->first();

        $targetAmount = $target ? (float) $target->targetAmount : 0;
        $targetProgress = $targetAmount > 0 ? min(100, round(($monthRevenue / $targetAmount) * 100)) : null;

        // Worked hours & Punctuality this month
        $monthAttendances = StaffAttendance::where('barberId', $barber->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $totalMinutes = (int) $monthAttendances->sum('workedMinutes');
        $totalHours = round($totalMinutes / 60, 1);
        $totalDaysWorked = $monthAttendances->count();
        $lateDays = $monthAttendances->where('status', 'late')->count();
        $punctualityRate = $totalDaysWorked > 0 ? round((($totalDaysWorked - $lateDays) / $totalDaysWorked) * 100) : 100;

        // Regular clients count
        $regularClientsCount = Appointment::where('barberId', $barber->id)
            ->where('status', 'completed')
            ->select('clientId')
            ->groupBy('clientId')
            ->havingRaw('count(*) >= 2')
            ->get()
            ->count();

        // Top Service
        $topServiceId = Appointment::where('barberId', $barber->id)
            ->where('status', 'completed')
            ->whereNotNull('serviceId')
            ->groupBy('serviceId')
            ->select('serviceId', DB::raw('count(*) as count'))
            ->orderByDesc('count')
            ->value('serviceId');

        $topService = $topServiceId ? Service::find($topServiceId) : null;

        // Upcoming appointments count today
        $todayAppointmentsCount = Appointment::where('barberId', $barber->id)
            ->whereDate('date', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        return [
            'todayStatus' => $todayStatus,
            'todayAttendance' => $todayAttendance,
            'monthRevenue' => $monthRevenue,
            'totalRevenue' => $totalRevenue,
            'monthAppointmentsCount' => $monthAppointmentsCount,
            'totalAppointmentsCount' => $totalAppointmentsCount,
            'estimatedCommission' => $estimatedCommission,
            'targetAmount' => $targetAmount,
            'targetProgress' => $targetProgress,
            'totalHours' => $totalHours,
            'totalDaysWorked' => $totalDaysWorked,
            'punctualityRate' => $punctualityRate,
            'regularClientsCount' => $regularClientsCount,
            'topService' => $topService,
            'todayAppointmentsCount' => $todayAppointmentsCount,
            'hireDateFormatted' => $barber->hireDate ? Carbon::parse($barber->hireDate)->format('d/m/Y') : 'Non renseignée',
            'seniority' => $barber->hireDate ? Carbon::parse($barber->hireDate)->diffForHumans(null, true) : 'N/D',
        ];
    }

    public function getAppointmentsList(): Collection
    {
        return $this->record->appointments()
            ->with(['client', 'service', 'sale'])
            ->orderBy('date', 'desc')
            ->orderBy('startTime', 'desc')
            ->take(25)
            ->get();
    }

    public function getAttendancesList(): Collection
    {
        return $this->record->attendances()
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();
    }

    public function getSalesList(): Collection
    {
        return $this->record->sales()
            ->with(['client', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();
    }

    public function getAbsencesList(): Collection
    {
        return $this->record->absences()
            ->orderBy('startDate', 'desc')
            ->take(20)
            ->get();
    }

    public function getPayrollsList(): Collection
    {
        return $this->record->payrolls()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();
    }

    public function getPhotosList(): Collection
    {
        return $this->record->appointmentPhotos()
            ->with(['client', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
