<?php

namespace App\Filament\Resources\PurchaseOrders\Helpers;

use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                ->helperText('Montant à ajouter au cumul des paiements.'),

            Textarea::make('payment_notes')
                ->label('Référence de paiement / Notes')
                ->placeholder('Ex: Virement bancaire réf #12345, Espèces remises au livreur...')
                ->rows(2),
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

        $order->recordPayment($amount);

        return $amount;
    }
}
