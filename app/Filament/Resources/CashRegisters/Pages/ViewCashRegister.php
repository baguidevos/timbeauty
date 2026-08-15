<?php

namespace App\Filament\Resources\CashRegisters\Pages;

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use App\Helpers\FormatHelper;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Afficher Session De Caisse';
    }

    protected function getHeaderActions(): array
    {
        /** @var CashRegister $record */
        $record = $this->getRecord();

        return [
            // Action 1 : Historique du jour (SlideOver)
            Action::make('history')
                ->label('Historique du jour')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->slideOver()
                ->modalWidth(Width::FourExtraLarge)
                ->modalHeading('Historique des opérations du jour')
                ->modalDescription('Journal complet des encaissements, décaissements et mouvements de caisse.')
                ->modalContent(fn () => view('filament.resources.cash-registers.components.cash-register-history', ['record' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),

            // Action 2 : Clôturer la caisse du jour
            Action::make('closeRegister')
                ->label('Clôturer la caisse du jour')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn (): bool => $record->isOpen())
                ->modalWidth(Width::Large)
                ->modalHeading('Clôturer la caisse du jour')
                ->modalDescription('Veuillez compter les espèces en caisse et renseigner le montant réel constaté.')
                ->schema(function () use ($record): array {
                    $sales = $record->sales()->where('paymentMethod', 'cash')->sum('total');
                    $expenses = $record->expenses()->where('paymentMethod', 'cash')->sum('amount');
                    $opening = (float) ($record->openingAmount ?? 0);
                    $theoretical = $opening + $sales - $expenses;

                    return [
                        Placeholder::make('theoretical_info')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="rounded-xl border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-900 dark:bg-sky-950/30">
                                    <span class="text-xs font-semibold text-sky-800 dark:text-sky-300">Solde théorique attendu en caisse :</span>
                                    <div class="mt-1 text-xl font-extrabold text-sky-600 dark:text-sky-400">'.FormatHelper::formatFCFA($theoretical).'</div>
                                    <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Calculé à partir du solde d\'ouverture + ventes en cash - dépenses en cash.</p>
                                </div>
                            ')),

                        TextInput::make('closingAmount')
                            ->label('Montant réel compté en caisse (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default($theoretical)
                            ->live(onBlur: true)
                            ->helperText('Saisissez le total physique des billets et pièces comptés.'),

                        Placeholder::make('variance_calc')
                            ->label('Écart de caisse')
                            ->content(function (Get $get) use ($theoretical) {
                                $counted = (float) ($get('closingAmount') ?? 0);
                                $diff = $counted - $theoretical;

                                if ($diff === 0.0 || $diff == 0) {
                                    return new HtmlString('<span class="inline-flex items-center gap-1 font-bold text-emerald-600">✓ Caisse parfaitement équilibrée (0 FCFA d\'écart)</span>');
                                } elseif ($diff > 0) {
                                    return new HtmlString('<span class="inline-flex items-center gap-1 font-bold text-sky-600">▲ Excédent de caisse : +'.FormatHelper::formatFCFA($diff).'</span>');
                                } else {
                                    return new HtmlString('<span class="inline-flex items-center gap-1 font-bold text-rose-600">▼ Déficit de caisse : '.FormatHelper::formatFCFA($diff).'</span>');
                                }
                            }),

                        Textarea::make('closingNotes')
                            ->label('Observations / Notes de clôture')
                            ->placeholder('Ex: RAS ou explication en cas d\'écart...')
                            ->rows(2),
                    ];
                })
                ->action(function (array $data) use ($record): void {
                    $sales = $record->sales()->where('paymentMethod', 'cash')->sum('total');
                    $expenses = $record->expenses()->where('paymentMethod', 'cash')->sum('amount');
                    $opening = (float) ($record->openingAmount ?? 0);
                    $theoretical = $opening + $sales - $expenses;
                    $counted = (float) ($data['closingAmount'] ?? 0);
                    $variance = $counted - $theoretical;

                    // Clôture
                    $record->close($counted);

                    // Si écart, enregistrer une transaction d'ajustement
                    if ($variance != 0) {
                        CashTransaction::create([
                            'cashRegisterId' => $record->id,
                            'type' => 'adjustment',
                            'amount' => abs($variance),
                            'description' => 'Ajustement clôture de caisse : '.($variance > 0 ? 'Excédent' : 'Déficit').($data['closingNotes'] ? ' ('.$data['closingNotes'].')' : ''),
                            'createdBy' => auth()->id(),
                        ]);
                    }

                    Notification::make()
                        ->title('Session de caisse clôturée avec succès')
                        ->body('Solde réel : '.FormatHelper::formatFCFA($counted))
                        ->success()
                        ->send();
                }),
        ];
    }
}
