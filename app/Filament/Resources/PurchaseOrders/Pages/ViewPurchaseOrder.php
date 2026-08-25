<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Helpers\FormatHelper;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.resources.purchase-orders.pages.view-purchase-order';

    public function getTitle(): string
    {
        return "Bon de Commande : {$this->record->reference}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            PurchaseOrderResource::getUrl('index') => 'Commandes',
            $this->record->reference,
        ];
    }

    protected function getHeaderActions(): array
    {
        $order = $this->record;
        $actions = [];

        // 0. Action Régler / Acompte (si pas totalement payée et pas annulée)
        if (! $order->isFullyPaid() && ! $order->isCancelled()) {
            $actions[] = Action::make('recordPayment')
                ->label('Régler / Acompte')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->modalWidth(Width::Medium)
                ->modalHeading("Règlement : {$order->reference}")
                ->modalDescription('Enregistrez un paiement partiel (acompte) ou le règlement total de la commande.')
                ->form(function () use ($order): array {
                    $total = FormatHelper::formatFCFA($order->totalAmount);
                    $paid = FormatHelper::formatFCFA($order->paidAmount);
                    $remaining = FormatHelper::formatFCFA($order->remaining_amount);
                    $pct = $order->payment_percentage;

                    return [
                        Placeholder::make('payment_summary')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs dark:border-amber-900/50 dark:bg-amber-950/30">
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
                            ->label('Option de paiement')
                            ->options([
                                'full' => 'Solder la totalité du reste dû ('.FormatHelper::formatFCFA($order->remaining_amount).')',
                                'partial' => 'Verser un acompte / montant spécifique',
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
                            ->required()
                            ->minValue(1)
                            ->maxValue($order->remaining_amount)
                            ->default($order->remaining_amount)
                            ->disabled(fn (Get $get): bool => $get('payment_mode') === 'full')
                            ->dehydrated()
                            ->helperText('Montant à ajouter au cumul des paiements.'),

                        Textarea::make('notes')
                            ->label('Référence de paiement / Notes')
                            ->placeholder('Ex: Virement bancaire réf #12345, Espèces remises au livreur...')
                            ->rows(2),
                    ];
                })
                ->action(function (array $data): void {
                    $order = $this->record;
                    $amountToAdd = (float) $data['amount'];
                    $order->recordPayment($amountToAdd);
                    $this->record = $order->fresh(['items.product', 'supplier', 'creator']);

                    $fresh = $this->record;
                    $statusText = $fresh->isFullyPaid() ? 'Commande totalement soldée (100%)' : 'Reste dû : '.FormatHelper::formatFCFA($fresh->remaining_amount);

                    Notification::make()
                        ->title('Paiement enregistré avec succès')
                        ->body('+'.FormatHelper::formatFCFA($amountToAdd)." versés. {$statusText}")
                        ->success()
                        ->send();
                });
        }

        // 1. Mark as Ordered (if pending)
        if ($order->status === 'pending') {
            $actions[] = Action::make('markAsOrdered')
                ->label('Marquer comme Envoyée')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Confirmer l\'envoi')
                ->modalDescription('Confirmez-vous que le bon de commande a été transmis au fournisseur ?')
                ->action(function (): void {
                    $this->record->update(['status' => 'ordered']);
                    Notification::make()
                        ->title('Commande marquée comme envoyée')
                        ->info()
                        ->send();
                });
        }

        // 2. Receive Order & Update Stock (if pending, ordered or partially_received)
        if (in_array($order->status, ['pending', 'ordered', 'partially_received'])) {
            $actions[] = Action::make('receiveOrder')
                ->label(fn () => $this->record->status === 'partially_received' ? 'Compléter la Réception' : 'Valider la Réception du Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalHeading('Réceptionner les articles commandés')
                ->modalDescription('Indiquez si la livraison est 100% conforme ou saisissez les quantités réelles reçues par article.')
                ->form(function () {
                    $order = $this->record;
                    $schema = [];

                    $schema[] = Toggle::make('is_conforming')
                        ->label('✅ Livraison 100% conforme (Quantités reçues = Quantités commandées)')
                        ->helperText('Laissez activé si tous les articles commandés sont livrés en intégralité.')
                        ->default(true)
                        ->live();

                    $itemInputs = [];
                    foreach ($order->items as $item) {
                        $productName = $item->productName ?: ($item->product?->name ?? 'Article');
                        $itemInputs[] = TextInput::make("received_items.{$item->id}")
                            ->label("{$productName}")
                            ->helperText("Commandé : {$item->quantity} | Déjà reçu : {$item->receivedQuantity}")
                            ->numeric()
                            ->default($item->quantity)
                            ->minValue(0)
                            ->maxValue($item->quantity)
                            ->required();
                    }

                    $schema[] = FormSection::make('Quantités réellement reçues par article')
                        ->description('Ajustez les quantités pour chaque produit en cas d\'écart ou de colis incomplet.')
                        ->schema($itemInputs)
                        ->columns(1)
                        ->visible(fn (Get $get) => ! $get('is_conforming'));

                    return $schema;
                })
                ->action(function (array $data): void {
                    $order = $this->record;
                    $isConforming = (bool) ($data['is_conforming'] ?? true);
                    $allFullyReceived = true;
                    $anyReceived = false;

                    foreach ($order->items as $item) {
                        $prevReceived = (int) ($item->receivedQuantity ?? 0);

                        if ($isConforming) {
                            $newReceived = (int) $item->quantity;
                        } else {
                            $newReceived = isset($data['received_items'][$item->id])
                                ? (int) $data['received_items'][$item->id]
                                : (int) $item->quantity;
                        }

                        $delta = max(0, $newReceived - $prevReceived);

                        if ($delta > 0 && $item->product) {
                            $item->product->increment('stockQuantity', $delta);

                            StockMovement::create([
                                'productId' => $item->product->id,
                                'type' => 'in',
                                'quantity' => $delta,
                                'reason' => 'purchase',
                                'reference' => $order->reference,
                            ]);
                        }

                        $item->update([
                            'receivedQuantity' => $newReceived,
                        ]);

                        if ($newReceived < $item->quantity) {
                            $allFullyReceived = false;
                        }
                        if ($newReceived > 0) {
                            $anyReceived = true;
                        }
                    }

                    $newStatus = $allFullyReceived ? 'received' : ($anyReceived ? 'partially_received' : $order->status);

                    $order->update([
                        'status' => $newStatus,
                        'receivedDate' => now(),
                    ]);

                    $this->record = $order->fresh(['items.product', 'supplier', 'creator']);

                    if ($allFullyReceived) {
                        Notification::make()
                            ->title('Commande entièrement réceptionnée')
                            ->body('Tous les stocks physiques ont été incrémentés à 100%.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Réception partielle enregistrée')
                            ->body('Les stocks ont été mis à jour selon les quantités effectivement reçues.')
                            ->warning()
                            ->send();
                    }
                });
        }

        // 3. Cancel Order (if not already cancelled/received)
        if (in_array($order->status, ['pending', 'ordered', 'partially_received'])) {
            $actions[] = Action::make('cancelOrder')
                ->label('Annuler la commande')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Annuler la commande')
                ->modalDescription('Êtes-vous sûr de vouloir annuler ce bon de commande ?')
                ->action(function (): void {
                    $this->record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->title('Commande annulée')
                        ->danger()
                        ->send();
                });
        }

        // 4. Edit Order
        if (! in_array($order->status, ['received'])) {
            $actions[] = EditAction::make()
                ->label('Modifier')
                ->color('gray');
        }

        return $actions;
    }

    public function getOrderStats(): array
    {
        $order = $this->record;
        $totalItems = $order->items->count();
        $totalQuantity = (int) $order->items->sum('quantity');
        $totalReceivedQuantity = (int) $order->items->sum('receivedQuantity');

        $totalAmount = (float) $order->totalAmount;
        $paidAmount = (float) $order->paidAmount;
        $remainingAmount = max(0, $totalAmount - $paidAmount);

        return [
            'totalItems' => $totalItems,
            'totalQuantity' => $totalQuantity,
            'totalReceivedQuantity' => $totalReceivedQuantity,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'supplier' => $order->supplier,
            'creator' => $order->creator,
        ];
    }

    public function getItemsList(): Collection
    {
        return $this->record->items()
            ->with('product.category')
            ->get();
    }
}
