<?php

namespace App\Filament\Resources\RevenueTargets\Pages;

use App\Filament\Resources\RevenueTargets\RevenueTargetResource;
use App\Models\Barber;
use App\Models\RevenueTarget;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class ListRevenueTargets extends ListRecords
{
    protected static string $resource = RevenueTargetResource::class;

    protected string $view = 'filament.resources.revenue-targets.pages.list-revenue-targets';

    public int $selectedMonth;

    public int $selectedYear;

    public array $monthNames = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    public function mount(): void
    {
        $this->selectedMonth = (int) now()->format('m');
        $this->selectedYear = (int) now()->format('Y');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Objectifs de Chiffre d\'Affaires';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Objectifs de Chiffre d\'Affaires</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Fixation et suivi des performances du salon et de l\'équipe</span>
                </div>
            </div>
        ');
    }

    public function prevMonth(): void
    {
        if ($this->selectedMonth === 1) {
            $this->selectedMonth = 12;
            $this->selectedYear--;
        } else {
            $this->selectedMonth--;
        }
    }

    public function nextMonth(): void
    {
        if ($this->selectedMonth === 12) {
            $this->selectedMonth = 1;
            $this->selectedYear++;
        } else {
            $this->selectedMonth++;
        }
    }

    public function getTargetsProperty(): Collection
    {
        return RevenueTarget::with('barber')
            ->where('month', $this->selectedMonth)
            ->where('year', $this->selectedYear)
            ->orderBy('type', 'desc')
            ->get();
    }

    public function getSummaryStatsProperty(): array
    {
        $targets = $this->targets;
        $shopCount = $targets->where('type', 'shop')->count();
        $barberCount = $targets->where('type', 'barber')->count();
        $totalTargetAmount = (float) $targets->sum('targetAmount');

        $avgProgress = $targets->count() > 0
            ? $targets->avg('progress_percentage')
            : 0;

        return [
            'shopCount' => $shopCount,
            'barberCount' => $barberCount,
            'totalTargetAmount' => $totalTargetAmount,
            'avgProgress' => (float) $avgProgress,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createTargetAction(),
        ];
    }

    public function createTargetAction(): Action
    {
        return Action::make('createTarget')
            ->label('Nouvel objectif')
            ->icon('heroicon-o-plus')
            ->color('warning')
            ->modalHeading('Définir un objectif de chiffre d\'affaires')
            ->modalWidth(Width::Large)
            ->schema([
                ToggleButtons::make('type')
                    ->label('Type d\'objectif')
                    ->options([
                        'shop' => 'Salon / Boutique',
                        'barber' => 'Personnel / Coiffeur',
                    ])
                    ->colors([
                        'shop' => 'warning',
                        'barber' => 'info',
                    ])
                    ->icons([
                        'shop' => 'heroicon-o-building-storefront',
                        'barber' => 'heroicon-o-user',
                    ])
                    ->default('shop')
                    ->required()
                    ->inline()
                    ->live()
                    ->columnSpanFull(),

                Select::make('barberId')
                    ->label('Membre du personnel concerné')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(function (Barber $record) {
                        $jobLabel = match ($record->jobTitle) {
                            'barber' => 'Coiffeur',
                            'manager' => 'Gérant',
                            'receptionist' => 'Réceptionniste',
                            'cashier' => 'Caissier',
                            default => 'Personnel',
                        };

                        return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                    })
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => $get('type') === 'barber')
                    ->visible(fn (Get $get) => $get('type') === 'barber')
                    ->columnSpanFull(),

                Select::make('month')
                    ->label('Mois')
                    ->options($this->monthNames)
                    ->default(fn () => $this->selectedMonth)
                    ->required()
                    ->native(false),

                Select::make('year')
                    ->label('Année')
                    ->options(array_combine(range(2024, 2030), range(2024, 2030)))
                    ->default(fn () => $this->selectedYear)
                    ->required()
                    ->native(false),

                TextInput::make('targetAmount')
                    ->label('Montant cible (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(1000)
                    ->placeholder('Ex: 500000')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                RevenueTarget::create([
                    'type' => $data['type'],
                    'barberId' => $data['type'] === 'barber' ? $data['barberId'] : null,
                    'month' => (int) $data['month'],
                    'year' => (int) $data['year'],
                    'targetAmount' => (float) $data['targetAmount'],
                    'createdBy' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Objectif créé avec succès')
                    ->success()
                    ->send();
            });
    }

    public function editTargetAction(): Action
    {
        return Action::make('editTarget')
            ->icon('heroicon-o-pencil-square')
            ->iconButton()
            ->color('gray')
            ->tooltip('Modifier l\'objectif')
            ->modalHeading('Modifier le montant de l\'objectif')
            ->modalWidth(Width::Medium)
            ->fillForm(function (array $arguments): array {
                $target = RevenueTarget::findOrFail($arguments['target']);

                return [
                    'targetAmount' => $target->targetAmount,
                ];
            })
            ->schema([
                TextInput::make('targetAmount')
                    ->label('Nouveau montant objectif (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(1000),
            ])
            ->action(function (array $arguments, array $data): void {
                $target = RevenueTarget::findOrFail($arguments['target']);
                $target->update([
                    'targetAmount' => (float) $data['targetAmount'],
                ]);

                Notification::make()
                    ->title('Objectif mis à jour')
                    ->success()
                    ->send();
            });
    }

    public function deleteTargetAction(): Action
    {
        return Action::make('deleteTarget')
            ->icon('heroicon-o-trash')
            ->iconButton()
            ->color('danger')
            ->tooltip('Supprimer l\'objectif')
            ->requiresConfirmation()
            ->modalHeading('Supprimer cet objectif ?')
            ->modalDescription('Êtes-vous sûr de vouloir supprimer cet objectif de chiffre d\'affaires ?')
            ->action(function (array $arguments): void {
                $target = RevenueTarget::findOrFail($arguments['target']);
                $target->delete();

                Notification::make()
                    ->title('Objectif supprimé')
                    ->success()
                    ->send();
            });
    }
}
