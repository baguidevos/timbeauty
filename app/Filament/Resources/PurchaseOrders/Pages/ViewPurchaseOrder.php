<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;

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

        // 2. Receive Order & Update Stock (if pending or ordered)
        if (in_array($order->status, ['pending', 'ordered'])) {
            $actions[] = Action::make('receiveOrder')
                ->label('Valider la Réception du Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Réceptionner la commande')
                ->modalDescription('Cette action va marquer la commande comme "Reçue", mettre à jour le stock physique de chaque produit et enregistrer les mouvements d\'entrée de stock.')
                ->action(function (): void {
                    $order = $this->record;

                    foreach ($order->items as $item) {
                        $item->update([
                            'receivedQuantity' => $item->quantity,
                        ]);

                        if ($item->product) {
                            $product = $item->product;
                            $product->increment('stockQuantity', (int) $item->quantity);

                            StockMovement::create([
                                'productId' => $product->id,
                                'type' => 'in',
                                'quantity' => (int) $item->quantity,
                                'reason' => 'purchase',
                                'reference' => $order->reference,
                            ]);
                        }
                    }

                    $order->update([
                        'status' => 'received',
                        'receivedDate' => now(),
                    ]);

                    Notification::make()
                        ->title('Commande réceptionnée avec succès')
                        ->body('Les stocks de tous les articles ont été automatiquement incrémentés.')
                        ->success()
                        ->send();
                });
        }

        // 3. Cancel Order (if not already cancelled/received)
        if (in_array($order->status, ['pending', 'ordered'])) {
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
        if ($order->status !== 'received') {
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
