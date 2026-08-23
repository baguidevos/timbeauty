<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Helpers\FormatHelper;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;

/**
 * @property Sale $record
 */
class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected string $view = 'filament.resources.sales.pages.view-sale';

    public function getTitle(): string
    {
        return "Vente #{$this->record->id}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            SaleResource::getUrl('index') => 'Ventes',
            "Vente #{$this->record->id}",
        ];
    }

    protected function getHeaderActions(): array
    {
        $sale = $this->record;
        $actions = [];

        // 1. Action Imprimer
        $actions[] = Action::make('print')
            ->label('Imprimer le Ticket')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->extraAttributes([
                'onclick' => 'window.print()',
            ]);

        // 2. Action Partager par WhatsApp au Client
        if ($sale->client && $sale->client->phone) {
            $phoneClean = preg_replace('/[^0-9]/', '', $sale->client->phone);
            $dateFormatted = $sale->created_at ? $sale->created_at->format('d/m/Y à H:i') : '';
            $totalFormatted = FormatHelper::formatFCFA($sale->total);
            $points = $sale->client->loyaltyPoints ?? 0;

            $itemsText = $sale->items->map(function ($item) {
                return "- {$item->name} (x{$item->quantity}) : ".FormatHelper::formatFCFA($item->total);
            })->implode("\n");

            $message = urlencode("💈 *BarberShop Pro - Reçu de Vente #{$sale->id}*\n\nBonjour {$sale->client->firstName},\nMerci pour votre visite du {$dateFormatted}.\n\n*Détail :*\n{$itemsText}\n\n*Total Réglé :* {$totalFormatted}\n*Mode :* {$sale->paymentMethod}\n*Solde Points Fidélité :* {$points} pts\n\nÀ très bientôt au salon !");

            $whatsappUrl = "https://wa.me/{$phoneClean}?text={$message}";

            $actions[] = Action::make('whatsapp')
                ->label('Envoyer WhatsApp')
                ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                ->color('success')
                ->url($whatsappUrl, shouldOpenInNewTab: true);
        }

        // 3. Action Modifier
        $actions[] = EditAction::make()
            ->slideOver();

        // 4. Action Annuler la vente (si terminée)
        if ($sale->status === 'completed') {
            $actions[] = Action::make('cancelSale')
                ->label('Annuler la Vente')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Annuler cette vente')
                ->modalDescription('Êtes-vous sûr de vouloir annuler cette vente ? Le statut passera à "Annulée" et les stocks des produits vendus seront réintégrés.')
                ->action(function (): void {
                    $sale = $this->record;

                    // Re-increment stock for products
                    foreach ($sale->items as $item) {
                        if ($item->type === 'product' && $item->itemId) {
                            $product = Product::find($item->itemId);
                            if ($product) {
                                $product->increment('stockQuantity', $item->quantity);

                                StockMovement::create([
                                    'productId' => $product->id,
                                    'type' => 'in',
                                    'quantity' => $item->quantity,
                                    'reason' => "Annulation de la vente #{$sale->id}",
                                    'reference' => "CANCEL-SALE-{$sale->id}",
                                ]);
                            }
                        }
                    }

                    $sale->update(['status' => 'cancelled']);

                    Notification::make()
                        ->title('Vente annulée avec succès')
                        ->body('Les stocks des produits ont été réintégrés.')
                        ->warning()
                        ->send();
                });
        }

        return $actions;
    }

    public function getSaleStats(): array
    {
        $sale = $this->record;
        $subtotal = (float) $sale->subtotal;
        $discount = (float) $sale->discountAmount;
        $total = (float) $sale->total;

        $discountPercentage = $subtotal > 0 && $discount > 0
            ? round(($discount / $subtotal) * 100)
            : 0;

        $servicesCount = (int) $sale->items->where('type', 'service')->sum('quantity');
        $productsCount = (int) $sale->items->where('type', 'product')->sum('quantity');
        $totalItems = (int) $sale->items->sum('quantity');

        $loyaltyEarned = (int) $sale->loyaltyPointTransactions
            ->where('points', '>', 0)
            ->sum('points');

        return [
            'subtotal' => $subtotal,
            'discountAmount' => $discount,
            'discountPercentage' => $discountPercentage,
            'total' => $total,
            'servicesCount' => $servicesCount,
            'productsCount' => $productsCount,
            'totalItems' => $totalItems,
            'loyaltyEarned' => $loyaltyEarned,
        ];
    }

    public function getItemsList(): Collection
    {
        return $this->record->items;
    }

    public function getAppliedPromotion(): ?Promotion
    {
        $promo = $this->record->promotion ?? $this->record->promotionUsages()->with('promotion')->first()?->promotion;
        if ($promo) {
            $promo->loadMissing('categories');
        }

        return $promo;
    }

    public function getServiceForItem(SaleItem $item): ?Service
    {
        if ($item->type !== 'service' || ! $item->itemId) {
            return null;
        }

        return Service::with('category')->find($item->itemId);
    }

    public function isItemEligibleForSalePromotion(SaleItem $item, ?Promotion $promotion): bool
    {
        if (! $promotion || $item->type !== 'service' || ! $item->itemId) {
            return false;
        }

        // Global promo applies to all services
        if ($promotion->categories->isEmpty()) {
            return true;
        }

        $service = $this->getServiceForItem($item);
        if (! $service || ! $service->categoryId) {
            return false;
        }

        return $promotion->categories->pluck('id')->contains($service->categoryId);
    }
}
