<?php

namespace App\Filament\Resources\PurchaseOrders\Helpers;

use App\Helpers\FormatHelper;
use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\OwnerAdvance;
use App\Models\PurchaseOrder;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class PurchaseOrderPaymentHelper
{
    /**
     * Retourne le schéma de formulaire pour enregistrer un paiement.
     *
     * @param  bool  $optionalWithToggle  Si true, englobe les champs sous un toggle optionnel
     */
    public static function getPaymentSchema(PurchaseOrder $order, bool $optionalWithToggle = false): array
    {
        if ($order->isFullyPaid()) {
            return [];
        }

        $total = FormatHelper::formatFCFA($order->totalAmount);
        $paid = FormatHelper::formatFCFA($order->paidAmount);
        $remaining = FormatHelper::formatFCFA($order->remaining_amount);
        $pct = $order->payment_percentage;

        $openRegister = CashRegister::where('status', 'open')->first();

        $paymentFields = [
            Placeholder::make('payment_summary')
                ->hiddenLabel()
                ->content(new HtmlString('
                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs dark:border-amber-900/50 dark:bg-amber-950/30">
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Total</span>
                                <div class="font-bold text-gray-900 dark:text-white">'.$total.'</div>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Déjà réglé</span>
                                <div class="font-bold text-emerald-600 dark:text-emerald-400">'.$paid.' ('.$pct.'%)</div>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Reste dû</span>
                                <div class="font-extrabold text-rose-600 dark:text-rose-400">'.$remaining.'</div>
                            </div>
                        </div>
                    </div>
                ')),

            Radio::make('payment_mode')
                ->label('Option de règlement')
                ->options([
                    'full' => 'Solder la totalité du reste dû ('.FormatHelper::formatFCFA($order->remaining_amount).')',
                    'partial' => 'Verser un acompte / montant partiel spécifique',
                ])
                ->default('full')
                ->live()
                ->afterStateUpdated(function ($state, Set $set) use ($order) {
                    if ($state === 'full') {
                        $set('amount', $order->remaining_amount);
                    }
                }),

            TextInput::make('amount')
                ->label('Montant du versement (FCFA)')
                ->numeric()
                ->required(fn (Get $get) => $optionalWithToggle ? (bool) $get('record_payment') : true)
                ->minValue(1)
                ->maxValue($order->remaining_amount)
                ->default($order->remaining_amount)
                ->disabled(fn (Get $get): bool => $get('payment_mode') === 'full')
                ->dehydrated()
                ->live()
                ->helperText('Montant à ajouter au cumul des paiements.'),

            Textarea::make('payment_notes')
                ->label('Référence de paiement / Notes')
                ->placeholder('Ex: Virement bancaire réf #12345, Espèces remises au livreur...')
                ->rows(2),

            Section::make('Gestion de la Caisse & Trésorerie')
                ->description('Déterminez si ce règlement doit impacter directement la caisse physique du salon.')
                ->schema([
                    Toggle::make('deduct_from_cash')
                        ->label('💰 Déduire ce règlement de la caisse physique')
                        ->helperText('Enregistre automatiquement une dépense d\'achat et un mouvement de sortie sur la caisse active.')
                        ->default(fn () => (bool) $openRegister)
                        ->live(),

                    Select::make('cash_register_id')
                        ->label('Session de Caisse')
                        ->options(function () {
                            return CashRegister::query()
                                ->orderByDesc('openedAt')
                                ->limit(10)
                                ->get()
                                ->mapWithKeys(function (CashRegister $reg) {
                                    $statusIcon = $reg->isOpen() ? '🟢 Ouverte' : '🔴 Fermée';
                                    $balance = FormatHelper::formatFCFA($reg->getTheoreticalBalance());
                                    $label = "Caisse #{$reg->id} ({$statusIcon}) — Solde disp. : {$balance}";

                                    return [$reg->id => $label];
                                });
                        })
                        ->default(fn () => $openRegister?->id)
                        ->required(fn (Get $get) => (bool) $get('deduct_from_cash'))
                        ->visible(fn (Get $get) => (bool) $get('deduct_from_cash'))
                        ->live(),

                    Placeholder::make('cash_balance_check')
                        ->hiddenLabel()
                        ->visible(fn (Get $get) => (bool) $get('deduct_from_cash') && $get('cash_register_id'))
                        ->content(function (Get $get) {
                            $registerId = $get('cash_register_id');
                            if (! $registerId) {
                                return '';
                            }

                            $register = CashRegister::find($registerId);
                            if (! $register) {
                                return '';
                            }

                            $balance = (float) $register->getTheoreticalBalance();
                            $paymentAmount = (float) ($get('amount') ?? 0);
                            $deficit = $paymentAmount - $balance;

                            if ($deficit <= 0) {
                                $remainingAfter = $balance - $paymentAmount;

                                return new HtmlString('
                                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 p-3 text-xs text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        <div class="flex items-center gap-2 font-semibold">
                                            <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span>Solde en caisse suffisant ('.FormatHelper::formatFCFA($balance).')</span>
                                        </div>
                                        <div class="mt-1 text-[11px] text-emerald-700 dark:text-emerald-400">
                                            Solde prévisionnel restant après ce règlement : <strong>'.FormatHelper::formatFCFA($remainingAfter).'</strong>
                                        </div>
                                    </div>
                                ');
                            }

                            return new HtmlString('
                                <div class="rounded-lg border border-rose-300 bg-rose-50/90 p-3 text-xs text-rose-900 dark:border-rose-800 dark:bg-rose-950/50 dark:text-rose-200">
                                    <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                                        <svg class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                        <span>Solde en caisse insuffisant ! (Disponible : '.FormatHelper::formatFCFA($balance).')</span>
                                    </div>
                                    <div class="mt-1 text-[11px]">
                                        Il manque <strong>'.FormatHelper::formatFCFA($deficit).'</strong> pour honorer ce versement de '.FormatHelper::formatFCFA($paymentAmount).'.
                                    </div>
                                </div>
                            ');
                        }),

                    Group::make([
                        Toggle::make('owner_advance_enabled')
                            ->label('🤝 Ajouter un apport du propriétaire pour combler le montant')
                            ->helperText('Crédite la caisse sous forme d\'avance propriétaire remboursable.')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),

                        Group::make([
                            Select::make('owner_advance_user_id')
                                ->label('Propriétaire / Apporteur')
                                ->options(fn () => User::pluck('name', 'id'))
                                ->default(fn () => auth()->id())
                                ->required(fn (Get $get) => (bool) $get('owner_advance_enabled'))
                                ->searchable()
                                ->preload(),

                            TextInput::make('owner_advance_amount')
                                ->label('Montant de l\'apport (FCFA)')
                                ->numeric()
                                ->minValue(1)
                                ->default(function (Get $get) {
                                    $registerId = $get('cash_register_id');
                                    $register = $registerId ? CashRegister::find($registerId) : null;
                                    $balance = $register ? (float) $register->getTheoreticalBalance() : 0;
                                    $paymentAmount = (float) ($get('amount') ?? 0);

                                    return max(1, $paymentAmount - $balance);
                                })
                                ->required(fn (Get $get) => (bool) $get('owner_advance_enabled'))
                                ->helperText('Montant de trésorerie injecté par le propriétaire.'),

                            TextInput::make('owner_advance_reason')
                                ->label('Motif de l\'apport')
                                ->default(fn () => "Apport pour règlement commande {$order->reference}")
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ])
                            ->visible(fn (Get $get) => (bool) $get('owner_advance_enabled'))
                            ->columns(2),
                    ])
                        ->visible(function (Get $get) {
                            if (! $get('deduct_from_cash') || ! $get('cash_register_id')) {
                                return false;
                            }
                            $register = CashRegister::find($get('cash_register_id'));
                            if (! $register) {
                                return false;
                            }
                            $balance = (float) $register->getTheoreticalBalance();
                            $paymentAmount = (float) ($get('amount') ?? 0);

                            return $paymentAmount > $balance;
                        }),
                ])
                ->compact()
                ->columnSpanFull(),
        ];

        if ($optionalWithToggle) {
            return [
                Fieldset::make('Règlement Fournisseur')
                    ->schema([
                        Toggle::make('record_payment')
                            ->label('💰 Enregistrer un versement / acompte lors de cette étape ?')
                            ->helperText('Reste dû sur la commande : '.FormatHelper::formatFCFA($order->remaining_amount))
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Group::make($paymentFields)
                            ->visible(fn (Get $get) => (bool) $get('record_payment'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ];
        }

        return $paymentFields;
    }

    /**
     * Traite l'enregistrement d'un paiement si les données sont fournies.
     *
     * @return float|null Montant payé ou null
     */
    public static function processPaymentIfPresent(PurchaseOrder $order, array $data, bool $optionalWithToggle = false): ?float
    {
        if ($optionalWithToggle && empty($data['record_payment'])) {
            return null;
        }

        $amount = isset($data['amount']) ? (float) $data['amount'] : (isset($data['payment_amount']) ? (float) $data['payment_amount'] : 0);

        if ($amount <= 0) {
            return null;
        }

        DB::transaction(function () use ($order, $amount, $data): void {
            // 1. Déduction en caisse si demandée
            if (! empty($data['deduct_from_cash'])) {
                $cashRegisterId = $data['cash_register_id'] ?? CashRegister::where('status', 'open')->value('id');

                // Si apport propriétaire activé
                if (! empty($data['owner_advance_enabled']) && ! empty($data['owner_advance_amount'])) {
                    $advanceAmount = (float) $data['owner_advance_amount'];
                    if ($advanceAmount > 0) {
                        OwnerAdvance::create([
                            'cashRegisterId' => $cashRegisterId,
                            'userId' => $data['owner_advance_user_id'] ?? auth()->id(),
                            'amount' => $advanceAmount,
                            'status' => 'pending',
                            'reason' => $data['owner_advance_reason'] ?? "Apport pour règlement commande {$order->reference}",
                            'notes' => $data['payment_notes'] ?? null,
                            'createdBy' => auth()->id(),
                        ]);
                    }
                }

                // Trouver la catégorie Dépense appropriée
                $category = ExpenseCategory::where('name', 'like', '%Achat Produits%')
                    ->orWhere('name', 'like', '%Consommables%')
                    ->first() ?? ExpenseCategory::first();

                // Enregistrer la dépense
                Expense::create([
                    'categoryId' => $category?->id,
                    'amount' => $amount,
                    'date' => now(),
                    'description' => "Règlement Bon de Commande {$order->reference}".(! empty($data['payment_notes']) ? " ({$data['payment_notes']})" : ''),
                    'beneficiary' => $order->supplier?->name ?? 'Fournisseur',
                    'paymentMethod' => 'cash',
                    'cashRegisterId' => $cashRegisterId,
                    'createdBy' => auth()->id(),
                ]);
            }

            // 2. Mettre à jour le montant payé sur le bon de commande
            $order->recordPayment($amount);
        });

        return $amount;
    }
}
