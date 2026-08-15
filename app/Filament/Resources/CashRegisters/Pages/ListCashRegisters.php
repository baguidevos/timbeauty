<?php

namespace App\Filament\Resources\CashRegisters\Pages;

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use App\Filament\Resources\CashRegisters\Widgets\CashRegisterStatsWidget;
use App\Helpers\FormatHelper;
use App\Models\CashRegister;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListCashRegisters extends ListRecords
{
    protected static string $resource = CashRegisterResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Sessions de Caisse';
    }

    public function getHeading(): Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v-.75C2.25 4.01 3.01 3.25 3.95 3.25h16.1c.94 0 1.7.76 1.7 1.7V6h-.75a.75.75 0 0 1-.75-.75V4.5m0 0H3.75m16.5 0v11.25c0 .94-.76 1.7-1.7 1.7H3.95c-.94 0-1.7-.76-1.7-1.7V6" />
                    </svg>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Sessions de Caisse</span>
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Gestion des ouvertures, clôtures et journaux de caisse</span>
                </div>
            </div>
        ');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CashRegisterStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        $openRegister = CashRegister::where('status', 'open')->first();
        $lastClosed = CashRegister::where('status', 'closed')->latest('closedAt')->first();
        $lastClosingAmount = $lastClosed ? (float) $lastClosed->closingAmount : 0;

        return [
            Action::make('openCashRegister')
                ->label($openRegister ? 'Accéder à la caisse ouverte' : 'Ouvrir la caisse')
                ->icon('heroicon-o-wallet')
                ->color($openRegister ? 'success' : 'primary')
                ->modalWidth(Width::Large)
                ->modalHeading('Ouvrir une nouvelle session de caisse')
                ->modalDescription('Définissez le solde initial d\'ouverture de la caisse.')
                ->visible(fn (): bool => ! $openRegister)
                ->schema([
                    Placeholder::make('previous_closing_info')
                        ->hiddenLabel()
                        ->content(function () use ($lastClosed, $lastClosingAmount) {
                            if ($lastClosed) {
                                return new HtmlString('
                                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 dark:border-amber-900 dark:bg-amber-950/30">
                                        <span class="text-xs font-semibold text-amber-800 dark:text-amber-300">Dernière clôture enregistrée :</span>
                                        <div class="mt-1 font-bold text-amber-900 dark:text-amber-200">
                                            '.FormatHelper::formatFCFA($lastClosingAmount).' (le '.$lastClosed->closedAt?->format('d/m/Y à H:i').')
                                        </div>
                                    </div>
                                ');
                            }

                            return new HtmlString('
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                                    Première session de caisse : saisissez le fond de caisse de départ.
                                </div>
                            ');
                        }),

                    TextInput::make('openingAmount')
                        ->label('Solde d\'ouverture (FCFA)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default($lastClosingAmount)
                        ->helperText('Montant reporté de la dernière clôture ou fond initial de démarrage.'),
                ])
                ->action(function (array $data) {
                    $register = CashRegister::create([
                        'openingAmount' => $data['openingAmount'] ?? 0,
                        'status' => 'open',
                        'openedAt' => now(),
                        'openedBy' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Caisse ouverte avec succès')
                        ->body('Solde initial : '.FormatHelper::formatFCFA((float) $register->openingAmount))
                        ->success()
                        ->send();

                    return redirect(CashRegisterResource::getUrl('view', ['record' => $register]));
                }),

            Action::make('viewOpenRegister')
                ->label('Accéder à la caisse en cours')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn (): bool => (bool) $openRegister)
                ->url(fn () => $openRegister ? CashRegisterResource::getUrl('view', ['record' => $openRegister]) : null),

            Action::make('settings')
                ->label('Paramètres')

                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->modalWidth(Width::Large)
                ->modalHeading('Configuration du mode de caisse')
                ->modalDescription('Définissez le comportement du système lors de la création d\'une vente ou dépense.')
                ->schema([
                    Radio::make('cash_register_mode')
                        ->label('Mode de fonctionnement')
                        ->options([
                            'auto_open' => '1. Ouverture automatique transparente (Recommandé)',
                            'flexible' => '2. Mode souple / Tolérant',
                            'strict' => '3. Mode strict (Blocage avec avertissement)',
                        ])
                        ->descriptions([
                            'auto_open' => 'Si aucune caisse n\'est ouverte, le système ouvre automatiquement la caisse du jour avec le report de la veille dès la première opération.',
                            'flexible' => 'Permet d\'enregistrer les ventes et dépenses librement. Dès que vous ouvrez la caisse dans la journée, toutes les opérations du jour y sont automatiquement rattachées.',
                            'strict' => 'Bloque l\'enregistrement d\'une vente ou dépense en espèces si aucune session de caisse n\'a été préalablement ouverte.',
                        ])
                        ->default(fn () => Setting::get('cash_register_mode', 'auto_open'))
                        ->required(),
                ])
                ->action(function (array $data): void {
                    Setting::set('cash_register_mode', $data['cash_register_mode']);

                    Notification::make()
                        ->title('Paramètres de caisse mis à jour')
                        ->success()
                        ->send();
                }),
        ];
    }
}
