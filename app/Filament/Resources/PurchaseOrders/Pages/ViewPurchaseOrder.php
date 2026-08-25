<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\Helpers\PurchaseOrderPaymentHelper;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

/**
 * @property PurchaseOrder $record
 */
class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.resources.purchase-orders.pages.view-purchase-order';

    public function getTitle(): string
    {
        return "Bon de Commande {$this->record->reference}";
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
                ->form(fn (): array => PurchaseOrderPaymentHelper::getPaymentSchema($this->record, optionalWithToggle: false))
                ->action(function (array $data): void {
                    $order = $this->record;
                    $amountPaid = PurchaseOrderPaymentHelper::processPaymentIfPresent($order, $data, optionalWithToggle: false);

                    $this->record = $order->fresh(['items.product', 'supplier', 'creator']);

                    if ($amountPaid) {
                        $fresh = $this->record;
                        $statusText = $fresh->isFullyPaid() ? 'Commande totalement soldée (100%)' : 'Reste dû : '.FormatHelper::formatFCFA($fresh->remaining_amount);

                        Notification::make()
                            ->title('Paiement enregistré avec succès')
                            ->body('+'.FormatHelper::formatFCFA($amountPaid)." versés. {$statusText}")
                            ->success()
                            ->send();
                    }
                });
        }

        // 1. Mark as Ordered (if pending) avec prompt de paiement optionnel
        if ($order->status === 'pending') {
            $actions[] = Action::make('markAsOrdered')
                ->label('Marquer comme Envoyée')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->modalWidth(Width::Medium)
                ->modalHeading('Confirmer l\'envoi de la commande')
                ->modalDescription('Confirmez-vous que le bon de commande a été transmis au fournisseur ?')
                ->form(fn (): array => PurchaseOrderPaymentHelper::getPaymentSchema($this->record, optionalWithToggle: true))
                ->action(function (array $data): void {
                    $this->record->update(['status' => 'ordered']);

                    // Traitement éventuel d'un acompte versé à la commande
                    $amountPaid = PurchaseOrderPaymentHelper::processPaymentIfPresent($this->record, $data, optionalWithToggle: true);

                    $this->record = $this->record->fresh(['items.product', 'supplier', 'creator']);

                    $paymentMsg = $amountPaid ? ' (Acompte de '.FormatHelper::formatFCFA($amountPaid).' enregistré)' : '';

                    Notification::make()
                        ->title('Commande marquée comme envoyée')
                        ->body("Le statut de la commande est désormais 'Envoyée'.{$paymentMsg}")
                        ->info()
                        ->send();
                });
        }

        // 2. Receive Order & Update Stock (if pending, ordered or partially_received) avec prompt de paiement optionnel
        if (in_array($order->status, ['pending', 'ordered', 'partially_received'])) {
            $actions[] = Action::make('receiveOrder')
                ->label(fn () => $this->record->status === 'partially_received' ? 'Compléter la Réception' : 'Valider la Réception du Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalWidth(Width::Large)
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

                    // Ajout factorisé du bloc optionnel de règlement
                    if (! $order->isFullyPaid()) {
                        foreach (PurchaseOrderPaymentHelper::getPaymentSchema($order, optionalWithToggle: true) as $paymentComponent) {
                            $schema[] = $paymentComponent;
                        }
                    }

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

                    // Traitement du paiement éventuel à la livraison
                    $amountPaid = PurchaseOrderPaymentHelper::processPaymentIfPresent($order, $data, optionalWithToggle: true);

                    $this->record = $order->fresh(['items.product', 'supplier', 'creator']);

                    $paymentMsg = $amountPaid ? ' — Règlement de '.FormatHelper::formatFCFA($amountPaid).' enregistré.' : '';

                    if ($allFullyReceived) {
                        Notification::make()
                            ->title('Commande entièrement réceptionnée')
                            ->body("Tous les stocks physiques ont été incrémentés à 100%.{$paymentMsg}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Réception partielle enregistrée')
                            ->body("Les stocks ont été mis à jour selon les quantités effectivement reçues.{$paymentMsg}")
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
