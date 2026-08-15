<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use App\Filament\Resources\Payrolls\Widgets\PayrollChartWidget;
use App\Filament\Resources\Payrolls\Widgets\PayrollStatsWidget;
use App\Models\Barber;
use App\Services\PayrollService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Gestion des Salaires & Paie';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Gestion des Salaires & Paie</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Calcul des rémunérations, commissions, avances et règlements</span>
                </div>
            </div>
        ');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Calculer les salaires pour le mois
            Action::make('calculateMonthly')
                ->label('Calculer les salaires')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->modalHeading('Calcul automatique des salaires')
                ->modalDescription('Cette action va calculer ou recalculer le salaire fixe, les commissions et les déductions pour l\'ensemble du personnel actif.')
                ->form([
                    Select::make('month')
                        ->label('Mois')
                        ->options([
                            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                        ])
                        ->default((int) now()->format('m'))
                        ->required()
                        ->native(false),
                    Select::make('year')
                        ->label('Année')
                        ->options(array_combine(range(2024, 2030), range(2024, 2030)))
                        ->default((int) now()->format('Y'))
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data): void {
                    $count = app(PayrollService::class)->calculateAll(
                        (int) $data['month'],
                        (int) $data['year']
                    );

                    Notification::make()
                        ->title('Salaires calculés avec succès')
                        ->body("{$count} fiches de paie générées pour la période ".sprintf('%02d/%d', $data['month'], $data['year']))
                        ->success()
                        ->send();
                }),

            // Ajouter une avance
            Action::make('addAdvance')
                ->label('Ajouter une avance')
                ->icon('heroicon-o-hand-raised')
                ->color('gray')
                ->modalHeading('Enregistrer un acompte / avance sur salaire')
                ->form([
                    Select::make('barberId')
                        ->label('Employé')
                        ->relationship('barber', 'firstName')
                        ->getOptionLabelFromRecordUsing(function (Barber $record) {
                            $jobLabel = match ($record->jobTitle) {
                                'barber' => 'Coiffeur',
                                'manager' => 'Gérant',
                                'receptionist' => 'Réceptionniste',
                                'cashier' => 'Caissier',
                                'cleaner' => 'Entretien',
                                default => 'Employé',
                            };

                            return "{$record->firstName} {$record->lastName} ({$jobLabel})";
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('amount')
                        ->label('Montant de l\'avance (FCFA)')
                        ->numeric()
                        ->required()
                        ->minValue(100),
                    Select::make('month')
                        ->label('Mois')
                        ->options([
                            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                        ])
                        ->default((int) now()->format('m'))
                        ->required()
                        ->native(false),
                    Select::make('year')
                        ->label('Année')
                        ->options(array_combine(range(2024, 2030), range(2024, 2030)))
                        ->default((int) now()->format('Y'))
                        ->required()
                        ->native(false),
                    Textarea::make('notes')
                        ->label('Motif de l\'avance')
                        ->placeholder('Ex: Demande urgente, acompte mi-mois...'),
                ])
                ->action(function (array $data): void {
                    app(PayrollService::class)->addAdvance(
                        (int) $data['barberId'],
                        (int) $data['month'],
                        (int) $data['year'],
                        (float) $data['amount'],
                        $data['notes'] ?? null
                    );

                    Notification::make()
                        ->title('Avance sur salaire enregistrée')
                        ->body('Montant déduit de la fiche du mois '.sprintf('%02d/%d', $data['month'], $data['year']))
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Nouvelle fiche')
                ->slideOver(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PayrollStatsWidget::class,
            PayrollChartWidget::class,
        ];
    }
}
