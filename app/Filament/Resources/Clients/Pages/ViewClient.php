<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\LoyaltyTier;
use App\Models\Sale;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected string $view = 'filament.resources.clients.pages.view-client';

    public string $activeTab = 'overview';

    public string $clientNote = '';

    public bool $isEditingNote = false;

    public function getTitle(): string
    {
        return "Fiche Client : {$this->record->getFullName()}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            ClientResource::getUrl('index') => 'Clients',
            $this->record->getFullName(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->clientNote = (string) ($this->record->notes ?? '');
    }

    public function saveNotes(): void
    {
        $this->record->update([
            'notes' => $this->clientNote,
        ]);

        $this->isEditingNote = false;

        Notification::make()
            ->title('Notes client mises à jour')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // 1. WhatsApp Action
        $rawPhone = $this->record->whatsapp ?: $this->record->phone;
        if ($rawPhone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strlen($cleanPhone) === 8) {
                $cleanPhone = '228'.$cleanPhone;
            }
            $actions[] = Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->url("https://wa.me/{$cleanPhone}?text=".urlencode("Bonjour {$this->record->firstName}, nous espérons que vous allez bien !"), true);
        }

        // 2. Direct POS sale action
        $actions[] = Action::make('posSale')
            ->label('Encaisser au POS')
            ->icon('heroicon-o-shopping-cart')
            ->color('warning')
            ->url(route('filament.admin.pages.pos', ['client' => $this->record->id]));

        // 3. New Appointment action
        $actions[] = Action::make('bookAppointment')
            ->label('Nouveau RDV')
            ->icon('heroicon-o-calendar-days')
            ->color('info')
            ->url(route('filament.admin.resources.appointments.index'));

        // 4. Standard Edit action
        $actions[] = EditAction::make()
            ->label('Modifier')
            ->color('gray');

        return $actions;
    }

    public function getClientStats(): array
    {
        $client = $this->record;
        $totalSpent = (float) $client->totalSpent;
        $totalVisits = (int) $client->totalVisits;
        $avgBasket = $totalVisits > 0 ? round($totalSpent / $totalVisits) : 0;

        // Birthday check
        $isBirthday = false;
        $age = null;
        if ($client->birthDate) {
            $birth = Carbon::parse($client->birthDate);
            $today = Carbon::today();
            $isBirthday = $birth->isBirthday($today);
            $age = $birth->age;
        }

        // Loyalty Tier & Progression
        $currentTier = $client->loyaltyTier;
        $nextTier = LoyaltyTier::where('status', 'active')
            ->where('minPoints', '>', $client->loyaltyPoints ?? 0)
            ->orderBy('minPoints', 'asc')
            ->first();

        $tierProgress = 100;
        $pointsNeeded = 0;
        if ($nextTier) {
            $currentBase = $currentTier ? (int) $currentTier->minPoints : 0;
            $tierSpan = max(1, (int) $nextTier->minPoints - $currentBase);
            $pointsInCurrentTier = max(0, (int) $client->loyaltyPoints - $currentBase);
            $tierProgress = min(100, max(0, round(($pointsInCurrentTier / $tierSpan) * 100)));
            $pointsNeeded = max(0, (int) $nextTier->minPoints - (int) $client->loyaltyPoints);
        }

        // Appointments attendance
        $totalAppointments = $client->appointments()->count();
        $completedAppointments = $client->appointments()->where('status', 'completed')->count();
        $cancelledAppointments = $client->appointments()->where('status', 'cancelled')->count();
        $attendanceRate = $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100) : 100;

        // Upcoming appointment
        $upcomingAppointment = $client->appointments()
            ->with(['barber', 'service'])
            ->whereDate('date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->orderBy('date', 'asc')
            ->orderBy('startTime', 'asc')
            ->first();

        // Favorite Barber
        $favoriteBarberId = Appointment::where('clientId', $client->id)
            ->whereNotNull('barberId')
            ->where('status', 'completed')
            ->groupBy('barberId')
            ->select('barberId', DB::raw('count(*) as count'))
            ->orderByDesc('count')
            ->value('barberId');

        $favoriteBarber = $favoriteBarberId ? Barber::find($favoriteBarberId) : null;

        // Favorite Service
        $favoriteServiceId = Appointment::where('clientId', $client->id)
            ->whereNotNull('serviceId')
            ->where('status', 'completed')
            ->groupBy('serviceId')
            ->select('serviceId', DB::raw('count(*) as count'))
            ->orderByDesc('count')
            ->value('serviceId');

        $favoriteService = $favoriteServiceId ? Service::find($favoriteServiceId) : null;

        return [
            'totalSpent' => $totalSpent,
            'totalVisits' => $totalVisits,
            'avgBasket' => $avgBasket,
            'loyaltyPoints' => (int) $client->loyaltyPoints,
            'isLoyal' => (bool) $client->isLoyal,
            'isBirthday' => $isBirthday,
            'age' => $age,
            'currentTier' => $currentTier,
            'nextTier' => $nextTier,
            'tierProgress' => $tierProgress,
            'pointsNeeded' => $pointsNeeded,
            'attendanceRate' => $attendanceRate,
            'totalAppointments' => $totalAppointments,
            'completedAppointments' => $completedAppointments,
            'cancelledAppointments' => $cancelledAppointments,
            'upcomingAppointment' => $upcomingAppointment,
            'favoriteBarber' => $favoriteBarber,
            'favoriteService' => $favoriteService,
            'firstVisit' => $client->firstVisitDate ? Carbon::parse($client->firstVisitDate)->format('d/m/Y') : 'Non renseignée',
            'lastVisit' => $client->lastVisitDate ? Carbon::parse($client->lastVisitDate)->format('d/m/Y') : null,
            'lastVisitHuman' => $client->lastVisitDate ? Carbon::parse($client->lastVisitDate)->diffForHumans() : 'Jamais',
        ];
    }

    public function getAppointmentsList(): Collection
    {
        return $this->record->appointments()
            ->with(['barber', 'service', 'sale'])
            ->orderBy('date', 'desc')
            ->orderBy('startTime', 'desc')
            ->take(20)
            ->get();
    }

    public function getSalesList(): Collection
    {
        return $this->record->sales()
            ->with(['barber', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }

    public function getLoyaltyTransactionsList(): Collection
    {
        return $this->record->loyaltyPointTransactions()
            ->with('sale')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }

    public function getPromotionUsagesList(): Collection
    {
        return $this->record->promotionUsages()
            ->with(['promotion', 'sale'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }

    public function getPhotosList(): Collection
    {
        return $this->record->appointmentPhotos()
            ->with(['barber', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
