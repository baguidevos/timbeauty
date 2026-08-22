<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    protected string $view = 'filament.resources.suppliers.pages.view-supplier';

    public string $activeTab = 'orders';

    public string $supplierNotes = '';

    public bool $isEditingNotes = false;

    public function getTitle(): string
    {
        return "Fiche Fournisseur : {$this->record->name}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            SupplierResource::getUrl('index') => 'Fournisseurs',
            $this->record->name,
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->supplierNotes = (string) ($this->record->notes ?? '');
    }

    public function saveNotes(): void
    {
        $this->record->update([
            'notes' => $this->supplierNotes,
        ]);

        $this->isEditingNotes = false;

        Notification::make()
            ->title('Notes fournisseur mises à jour')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // 1. WhatsApp Action
        if ($this->record->phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $this->record->phone);
            if (strlen($cleanPhone) === 8) {
                $cleanPhone = '228'.$cleanPhone;
            }
            $actions[] = Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->url("https://wa.me/{$cleanPhone}?text=".urlencode('Bonjour, nous vous contactons concernant nos commandes de fournitures.'), true);
        }

        // 2. Direct Call Action
        if ($this->record->phone) {
            $actions[] = Action::make('call')
                ->label('Appeler')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->url("tel:{$this->record->phone}");
        }

        // 3. New Order Action
        $actions[] = Action::make('newOrder')
            ->label('Nouvelle commande')
            ->icon('heroicon-o-plus-circle')
            ->color('warning')
            ->url(PurchaseOrderResource::getUrl('index'));

        // 4. Edit Action
        $actions[] = EditAction::make()
            ->label('Modifier')
            ->color('gray');

        return $actions;
    }

    public function getSupplierStats(): array
    {
        $supplier = $this->record;

        $totalOrders = $supplier->purchaseOrders()->count();
        $receivedOrders = $supplier->purchaseOrders()->where('status', 'received')->count();
        $pendingOrders = $supplier->purchaseOrders()->whereIn('status', ['pending', 'ordered'])->count();
        $cancelledOrders = $supplier->purchaseOrders()->where('status', 'cancelled')->count();

        $totalSpent = (float) $supplier->purchaseOrders()->where('status', '!=', 'cancelled')->sum('totalAmount');
        $totalPaid = (float) $supplier->purchaseOrders()->where('status', '!=', 'cancelled')->sum('paidAmount');
        $balanceDue = max(0, $totalSpent - $totalPaid);

        $products = $supplier->products;
        $totalProducts = $products->count();
        $lowStockProducts = $products->filter(fn ($p) => $p->isLowStock())->count();

        $lastOrder = $supplier->purchaseOrders()->orderBy('orderDate', 'desc')->first();

        return [
            'totalOrders' => $totalOrders,
            'receivedOrders' => $receivedOrders,
            'pendingOrders' => $pendingOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalSpent' => $totalSpent,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
            'totalProducts' => $totalProducts,
            'lowStockProducts' => $lowStockProducts,
            'lastOrder' => $lastOrder,
        ];
    }

    public function getOrdersList(): Collection
    {
        return $this->record->purchaseOrders()
            ->with(['items.product', 'creator'])
            ->orderBy('orderDate', 'desc')
            ->take(30)
            ->get();
    }

    public function getProductsList(): Collection
    {
        return $this->record->products()
            ->with('category')
            ->orderBy('name', 'asc')
            ->get();
    }
}
