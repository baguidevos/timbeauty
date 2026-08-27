<?php

namespace App\Filament\Resources\CashRegisters\Pages;

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use App\Helpers\FormatHelper;
use App\Models\BankDeposit;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\OwnerAdvance;
use App\Models\OwnerRefund;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var CashRegister $record */
        $record = $this->getRecord();
        $status = $record->isOpen() ? 'Ouverte' : 'Fermée';

        return "Session Caisse #{$record->id} ({$status})";
    }

    public function getBreadcrumbs(): array
    {
        return [
            CashRegisterResource::getUrl('index') => 'Caisses',
            "Session #{$this->getRecord()->id}",
        ];
    }

    protected function getHeaderActions(): array
    {
        /** @var CashRegister $record */
        $record = $this->getRecord();
        $actions = [];

        // 1. Historique du jour (SlideOver)
        $actions[] = Action::make('history')
            ->label('Historique')
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->slideOver()
            ->modalWidth(Width::FourExtraLarge)
            ->modalHeading('Historique des opérations du jour')
            ->modalDescription('Journal complet des encaissements, décaissements et mouvements de caisse.')
            ->modalContent(fn () => view('filament.resources.cash-registers.components.cash-register-history', ['record' => $record]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer');

        if ($record->isOpen()) {
            $isThresholdReached = $record->isBankDepositThresholdReached();

            // 2. Groupe d'actions de trésorerie (Apports, Remboursements, Dépôts Banque)
            $treasuryActions = [
                // 2.1 Apport / Avance Propriétaire
                Action::make('ownerAdvance')
                    ->label('Apport Propriétaire')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->modalWidth(Width::Medium)
                    ->modalHeading('Enregistrer un apport / avance de fonds')
                    ->modalDescription('Injection de fonds personnels par le propriétaire (ex: paie des salariés).')
                    ->schema([
                        Select::make('userId')
                            ->label('Propriétaire / Apporteur')
                            ->options(User::pluck('name', 'id'))
                            ->default(auth()->id())
                            ->required()
                            ->searchable()
                            ->native(false),
                        TextInput::make('amount')
                            ->label('Montant apporté (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText('Ce montant sera ajouté à la caisse et suivi en dette à rembourser.'),
                        TextInput::make('reason')
                            ->label('Motif de l\'apport')
                            ->placeholder('Ex: Paie des salaires, Achat urgent de stock...')
                            ->default('Avance trésorerie / Paie du personnel')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label('Notes complémentaires')
                            ->rows(2),
                    ])
                    ->action(function (array $data) use ($record): void {
                        $advance = OwnerAdvance::create([
                            'cashRegisterId' => $record->id,
                            'userId' => $data['userId'],
                            'amount' => $data['amount'],
                            'reason' => $data['reason'],
                            'notes' => $data['notes'] ?? null,
                            'createdBy' => auth()->id(),
                            'status' => 'pending',
                        ]);

                        Notification::make()
                            ->title('Apport propriétaire enregistré')
                            ->body('+'.FormatHelper::formatFCFA((float) $data['amount']).' ajoutés à la caisse.')
                            ->success()
                            ->send();
                    }),

                // 2.2 Rembourser Propriétaire
                Action::make('ownerRefund')
                    ->label('Rembourser Propriétaire')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->visible(fn (): bool => OwnerAdvance::whereIn('status', ['pending', 'partially_refunded'])->exists())
                    ->modalWidth(Width::Medium)
                    ->modalHeading('Rembourser une avance propriétaire')
                    ->modalDescription('Prélevez de l\'argent en caisse pour rembourser une avance propriétaire.')
                    ->schema(function () use ($record): array {
                        $currentBalance = $record->getTheoreticalBalance();
                        $pendingAdvances = OwnerAdvance::whereIn('status', ['pending', 'partially_refunded'])->with('user')->get();
                        $totalDebt = $pendingAdvances->sum(fn ($a) => $a->remaining_amount);

                        $options = [];
                        foreach ($pendingAdvances as $adv) {
                            $remaining = FormatHelper::formatFCFA($adv->remaining_amount);
                            $owner = $adv->user?->name ?? 'Propriétaire';
                            $date = $adv->created_at?->format('d/m/Y') ?? '';
                            $options[$adv->id] = "#{$adv->id} - {$owner} ({$adv->reason}) | Reste : {$remaining} ({$date})";
                        }

                        return [
                            Placeholder::make('debt_summary')
                                ->hiddenLabel()
                                ->content(new HtmlString('
                                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs dark:border-amber-900/50 dark:bg-amber-950/30">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-amber-800 dark:text-amber-300">Dette totale en cours :</span>
                                            <span class="font-bold text-amber-700 dark:text-amber-400">'.FormatHelper::formatFCFA($totalDebt).'</span>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between border-t border-amber-200/60 pt-1 dark:border-amber-800/40">
                                            <span class="text-gray-600 dark:text-gray-400">Solde disponible en caisse :</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">'.FormatHelper::formatFCFA($currentBalance).'</span>
                                        </div>
                                    </div>
                                ')),

                            Select::make('ownerAdvanceId')
                                ->label('Sélectionner l\'avance à rembourser')
                                ->options($options)
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state) {
                                        $advance = OwnerAdvance::find($state);
                                        if ($advance) {
                                            $set('amount', $advance->remaining_amount);
                                        }
                                    }
                                }),

                            TextInput::make('amount')
                                ->label('Montant à rembourser (FCFA)')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->live(onBlur: true)
                                ->helperText(function (Get $get) {
                                    $advId = $get('ownerAdvanceId');
                                    if (! $advId) {
                                        return null;
                                    }
                                    $advance = OwnerAdvance::find($advId);

                                    return $advance ? 'Reste dû sur cette avance : '.FormatHelper::formatFCFA($advance->remaining_amount) : null;
                                }),

                            Textarea::make('notes')
                                ->label('Notes')
                                ->placeholder('Ex: Remboursement partiel/total...')
                                ->rows(2),
                        ];
                    })
                    ->action(function (array $data) use ($record): void {
                        $advance = OwnerAdvance::findOrFail($data['ownerAdvanceId']);
                        $amount = (float) $data['amount'];
                        $currentBalance = $record->getTheoreticalBalance();

                        if ($amount > $advance->remaining_amount) {
                            Notification::make()
                                ->title('Montant invalide')
                                ->body('Le montant ne peut pas dépasser le reste dû ('.FormatHelper::formatFCFA($advance->remaining_amount).').')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($amount > $currentBalance) {
                            Notification::make()
                                ->title('Solde de caisse insuffisant')
                                ->body('La caisse ne dispose que de '.FormatHelper::formatFCFA($currentBalance).' disponibles.')
                                ->danger()
                                ->send();

                            return;
                        }

                        OwnerRefund::create([
                            'ownerAdvanceId' => $advance->id,
                            'cashRegisterId' => $record->id,
                            'amount' => $amount,
                            'paymentMethod' => 'cash',
                            'notes' => $data['notes'] ?? null,
                            'createdBy' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Remboursement effectué')
                            ->body('-'.FormatHelper::formatFCFA($amount).' prélevés de la caisse.')
                            ->success()
                            ->send();
                    }),

                // 2.3 Dépôt en banque (Écrémage)
                Action::make('bankDeposit')
                    ->label('Dépôt en Banque (Écrémage)')
                    ->icon('heroicon-o-building-library')
                    ->color('info')
                    ->modalWidth(Width::Large)
                    ->modalHeading('Remise d\'espèces en banque (Écrémage)')
                    ->modalDescription('Enregistrez un retrait d\'espèces pour versement sur le compte bancaire du salon.')
                    ->schema(function () use ($record): array {
                        $balance = $record->getTheoreticalBalance();
                        $threshold = CashRegister::getBankDepositThreshold();
                        $defaultBank = Setting::get('default_bank_name', 'Ecobank Togo');
                        $defaultAccount = Setting::get('default_bank_account', '');

                        return [
                            Placeholder::make('balance_alert')
                                ->hiddenLabel()
                                ->content(new HtmlString('
                                    <div class="rounded-xl border '.($balance >= $threshold ? 'border-amber-300 bg-amber-50/80 dark:border-amber-900/60 dark:bg-amber-950/40' : 'border-sky-200 bg-sky-50/70 dark:border-sky-900/40 dark:bg-sky-950/30').' p-3.5 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Solde disponible en caisse :</span>
                                            <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">'.FormatHelper::formatFCFA($balance).'</span>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                            <span>Seuil conseillé : '.FormatHelper::formatFCFA($threshold).'</span>
                                            '.($balance >= $threshold ? '<span class="font-bold text-amber-700 dark:text-amber-400">⚠️ Seuil dépassé - Dépôt conseillé</span>' : '<span>Solde normal</span>').'
                                        </div>
                                    </div>
                                ')),

                            TextInput::make('amount')
                                ->label('Montant à déposer en banque (FCFA)')
                                ->numeric()
                                ->required()
                                ->minValue(1),

                            TextInput::make('bankName')
                                ->label('Banque réceptrice')
                                ->default($defaultBank)
                                ->required()
                                ->maxLength(100),

                            TextInput::make('bankAccountNumber')
                                ->label('Numéro de compte')
                                ->default($defaultAccount)
                                ->placeholder('Ex: TG001 01234 56789012345 67')
                                ->maxLength(100),

                            TextInput::make('depositSlipNumber')
                                ->label('N° de bordereau / Reçu de versement')
                                ->placeholder('Ex: BORD-987654')
                                ->maxLength(100),

                            Select::make('depositedBy')
                                ->label('Responsable du dépôt (Coursier/Gérant)')
                                ->options(User::pluck('name', 'id'))
                                ->default(auth()->id())
                                ->searchable()
                                ->native(false),

                            FileUpload::make('depositSlipPhoto')
                                ->label('Photo / Scan du bordereau bancaire')
                                ->image()
                                ->directory('bank-deposits')
                                ->maxSize(5120),

                            Textarea::make('notes')
                                ->label('Observations')
                                ->rows(2),
                        ];
                    })
                    ->action(function (array $data) use ($record): void {
                        $amount = (float) $data['amount'];
                        $currentBalance = $record->getTheoreticalBalance();

                        if ($amount > $currentBalance) {
                            Notification::make()
                                ->title('Solde insuffisant')
                                ->body('Impossible de retirer '.FormatHelper::formatFCFA($amount).' : la caisse ne contient que '.FormatHelper::formatFCFA($currentBalance).'.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $deposit = BankDeposit::create([
                            'cashRegisterId' => $record->id,
                            'amount' => $amount,
                            'bankName' => $data['bankName'],
                            'bankAccountNumber' => $data['bankAccountNumber'] ?? null,
                            'depositSlipNumber' => $data['depositSlipNumber'] ?? null,
                            'depositSlipPhoto' => $data['depositSlipPhoto'] ?? null,
                            'depositedBy' => $data['depositedBy'] ?? auth()->id(),
                            'depositDate' => now(),
                            'notes' => $data['notes'] ?? null,
                            'createdBy' => auth()->id(),
                            'status' => 'confirmed',
                        ]);

                        Notification::make()
                            ->title('Remise en banque enregistrée')
                            ->body("Réf: {$deposit->reference} — ".FormatHelper::formatFCFA($amount).' sortis de caisse.')
                            ->success()
                            ->send();
                    }),
            ];

            $actions[] = ActionGroup::make($treasuryActions)
                ->label($isThresholdReached ? '⚠️ Trésorerie (Seuil Requis)' : 'Trésorerie')
                ->icon('heroicon-o-banknotes')
                ->color($isThresholdReached ? 'warning' : 'primary')
                ->button();

            // 3. Clôturer la caisse
            $actions[] = Action::make('closeRegister')
                ->label('Clôturer la caisse')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->modalWidth(Width::Large)
                ->modalHeading('Clôturer la caisse du jour')
                ->modalDescription('Veuillez compter les espèces en caisse et renseigner le montant réel constaté.')
                ->schema(function () use ($record): array {
                    $theoretical = $record->getTheoreticalBalance();

                    return [
                        Placeholder::make('theoretical_info')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="rounded-xl border border-sky-200 bg-sky-50/70 p-4 dark:border-sky-900 dark:bg-sky-950/30">
                                    <span class="text-xs font-semibold text-sky-800 dark:text-sky-300">Solde théorique attendu en caisse :</span>
                                    <div class="mt-1 text-xl font-extrabold text-sky-600 dark:text-sky-400">'.FormatHelper::formatFCFA($theoretical).'</div>
                                    <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Calculé à partir de l\'ouverture + ventes + apports - dépenses - remboursements - dépôts banque.</p>
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
                    $theoretical = $record->getTheoreticalBalance();
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
                });
        }

        return $actions;
    }
}
