<?php

namespace App\Filament\Pages;

use App\Helpers\FormatHelper;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Client;
use App\Models\LoyaltyPointTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\StockMovement;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Pos extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Point de Vente (POS)';

    protected static ?string $title = 'Point de Vente (POS)';

    protected string $view = 'filament.pages.pos';

    // Cart state
    public array $cart = [];

    public ?int $clientId = null;

    public ?int $barberId = null;

    public string $discountType = 'percentage'; // 'percentage' | 'fixed'

    public float $discountValue = 0;

    public string $paymentMethod = 'cash';

    public string $notes = '';

    // Search and filters
    public string $activeTab = 'services'; // 'services' | 'products'

    public string $searchQuery = '';

    public string $clientSearch = '';

    public ?int $selectedCategoryId = null;

    // Modals
    public bool $showQuickClientModal = false;

    public string $quickClientFirstName = '';

    public string $quickClientLastName = '';

    public string $quickClientPhone = '';

    public ?string $quickClientGender = 'male';

    public bool $showReceiptModal = false;

    public ?int $lastSaleId = null;

    public bool $processing = false;

    /**
     * Hydrate cart state from session.
     */
    public function mount(): void
    {
        if (session()->has('pos_cart')) {
            $saved = session('pos_cart', []);
            $this->cart = $saved['cart'] ?? [];
            $this->clientId = $saved['clientId'] ?? null;
            $this->barberId = $saved['barberId'] ?? null;
            $this->discountType = $saved['discountType'] ?? 'percentage';
            $this->discountValue = (float) ($saved['discountValue'] ?? 0);
            $this->paymentMethod = $saved['paymentMethod'] ?? 'cash';
            $this->notes = $saved['notes'] ?? '';
        }
    }

    /**
     * Persist current cart state in user session.
     */
    public function saveCartToSession(): void
    {
        session(['pos_cart' => [
            'cart' => $this->cart,
            'clientId' => $this->clientId,
            'barberId' => $this->barberId,
            'discountType' => $this->discountType,
            'discountValue' => $this->discountValue,
            'paymentMethod' => $this->paymentMethod,
            'notes' => $this->notes,
        ]]);
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['clientId', 'barberId', 'discountType', 'discountValue', 'paymentMethod', 'notes'])) {
            $this->saveCartToSession();
        }
    }

    /**
     * Add item (service or product) to the cart.
     */
    public function addToCart(string $type, int $itemId): void
    {
        if ($type === 'service') {
            $service = Service::find($itemId);
            if (! $service || $service->status !== 'active') {
                Notification::make()
                    ->title('Prestation non disponible')
                    ->warning()
                    ->send();

                return;
            }

            $key = 'service_'.$itemId;
            if (isset($this->cart[$key])) {
                $this->cart[$key]['quantity']++;
            } else {
                $this->cart[$key] = [
                    'type' => 'service',
                    'itemId' => $service->id,
                    'name' => $service->name,
                    'unitPrice' => (float) $service->price,
                    'duration' => $service->duration,
                    'quantity' => 1,
                ];
            }

            $this->saveCartToSession();
            Notification::make()
                ->title($service->name.' ajouté')
                ->success()
                ->duration(1500)
                ->send();
        } elseif ($type === 'product') {
            $product = Product::find($itemId);
            if (! $product || $product->status !== 'active') {
                Notification::make()
                    ->title('Produit non disponible')
                    ->warning()
                    ->send();

                return;
            }

            if ($product->stockQuantity <= 0) {
                Notification::make()
                    ->title('Produit en rupture de stock')
                    ->danger()
                    ->send();

                return;
            }

            $key = 'product_'.$itemId;
            if (isset($this->cart[$key])) {
                if ($this->cart[$key]['quantity'] + 1 > $product->stockQuantity) {
                    Notification::make()
                        ->title('Stock insuffisant (max : '.$product->stockQuantity.')')
                        ->warning()
                        ->send();

                    return;
                }
                $this->cart[$key]['quantity']++;
            } else {
                $this->cart[$key] = [
                    'type' => 'product',
                    'itemId' => $product->id,
                    'name' => $product->name,
                    'unitPrice' => (float) $product->sellingPrice,
                    'stockQuantity' => $product->stockQuantity,
                    'quantity' => 1,
                ];
            }

            $this->saveCartToSession();
            Notification::make()
                ->title($product->name.' ajouté')
                ->success()
                ->duration(1500)
                ->send();
        }
    }

    /**
     * Update quantity of an item in cart.
     */
    public function updateQuantity(string $key, int $delta): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $item = $this->cart[$key];
        $newQty = $item['quantity'] + $delta;

        if ($newQty <= 0) {
            $this->removeFromCart($key);

            return;
        }

        if ($item['type'] === 'product') {
            $product = Product::find($item['itemId']);
            if ($product && $newQty > $product->stockQuantity) {
                Notification::make()
                    ->title('Stock maximum atteint ('.$product->stockQuantity.')')
                    ->warning()
                    ->send();

                return;
            }
        }

        $this->cart[$key]['quantity'] = $newQty;
        $this->saveCartToSession();
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(string $key): void
    {
        if (isset($this->cart[$key])) {
            unset($this->cart[$key]);
            $this->saveCartToSession();
        }
    }

    /**
     * Clear all items in cart.
     */
    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountValue = 0;
        $this->notes = '';
        session()->forget('pos_cart');

        Notification::make()
            ->title('Panier vidé')
            ->info()
            ->duration(2000)
            ->send();
    }

    /**
     * Create a new walk-in client quickly.
     */
    public function createQuickClient(): void
    {
        $this->validate([
            'quickClientFirstName' => 'required|string|max:100',
            'quickClientLastName' => 'required|string|max:100',
            'quickClientPhone' => 'required|string|max:25',
        ], [
            'quickClientFirstName.required' => 'Le prénom est requis.',
            'quickClientLastName.required' => 'Le nom est requis.',
            'quickClientPhone.required' => 'Le téléphone est requis.',
        ]);

        $client = Client::create([
            'firstName' => trim($this->quickClientFirstName),
            'lastName' => trim($this->quickClientLastName),
            'phone' => trim($this->quickClientPhone),
            'gender' => $this->quickClientGender ?? 'male',
            'firstVisitDate' => now()->toDateString(),
            'lastVisitDate' => now()->toDateString(),
            'totalVisits' => 0,
            'totalSpent' => 0,
        ]);

        $this->clientId = $client->id;
        $this->quickClientFirstName = '';
        $this->quickClientLastName = '';
        $this->quickClientPhone = '';
        $this->showQuickClientModal = false;
        $this->clientSearch = '';

        $this->saveCartToSession();
        $this->dispatch('close-modal', id: 'quick-client-modal');

        Notification::make()
            ->title('Client '.$client->getFullName().' créé et sélectionné !')
            ->success()
            ->send();
    }

    /**
     * Process checkout / encaisser.
     */
    public function processSale(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Le panier est vide')
                ->danger()
                ->send();

            return;
        }

        // Validate products stock
        foreach ($this->cart as $item) {
            if ($item['type'] === 'product') {
                $product = Product::find($item['itemId']);
                if (! $product || $product->stockQuantity < $item['quantity']) {
                    Notification::make()
                        ->title('Stock insuffisant pour '.$item['name'])
                        ->danger()
                        ->send();

                    return;
                }
            }
        }

        $this->processing = true;

        try {
            DB::beginTransaction();

            $subtotal = $this->getSubtotal();
            $discountAmount = $this->getDiscountAmount();
            $total = $this->getTotal();

            $sale = Sale::create([
                'clientId' => $this->clientId ?: null,
                'barberId' => $this->barberId ?: null,
                'subtotal' => $subtotal,
                'discountAmount' => $discountAmount,
                'total' => $total,
                'paymentMethod' => $this->paymentMethod,
                'status' => 'completed',
                'notes' => $this->notes ?: null,
                'createdBy' => auth()->id(),
            ]);

            foreach ($this->cart as $item) {
                $lineTotal = $item['unitPrice'] * $item['quantity'];

                SaleItem::create([
                    'saleId' => $sale->id,
                    'type' => $item['type'],
                    'itemId' => $item['itemId'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unitPrice' => $item['unitPrice'],
                    'discount' => 0,
                    'total' => $lineTotal,
                ]);

                // Decrement stock and log movement for products
                if ($item['type'] === 'product') {
                    $product = Product::find($item['itemId']);
                    if ($product) {
                        $product->decrement('stockQuantity', $item['quantity']);

                        StockMovement::create([
                            'productId' => $product->id,
                            'type' => 'out',
                            'quantity' => $item['quantity'],
                            'reason' => 'Vente en caisse #'.$sale->id,
                            'reference' => 'SALE-'.$sale->id,
                        ]);
                    }
                }
            }

            // Update client loyalty & stats if client is attached
            if ($sale->clientId) {
                $client = Client::find($sale->clientId);
                if ($client) {
                    $client->increment('totalVisits');
                    $client->increment('totalSpent', $total);
                    $client->update(['lastVisitDate' => now()->toDateString()]);

                    // Earn loyalty points if loyalty enabled
                    $pointsRate = (float) Setting::get('points_per_fcfa', 0.001);
                    $earnedPoints = (int) round($total * $pointsRate);

                    if ($earnedPoints > 0) {
                        $client->increment('loyaltyPoints', $earnedPoints);

                        LoyaltyPointTransaction::create([
                            'clientId' => $client->id,
                            'points' => $earnedPoints,
                            'type' => 'earn',
                            'description' => 'Points gagnés pour la vente #'.$sale->id,
                            'saleId' => $sale->id,
                            'reference' => 'SALE-'.$sale->id,
                        ]);
                    }
                }
            }

            DB::commit();

            // Clear cart & session
            $this->cart = [];
            $this->clientId = null;
            $this->barberId = null;
            $this->discountValue = 0;
            $this->notes = '';
            session()->forget('pos_cart');

            $this->lastSaleId = $sale->id;
            $this->showReceiptModal = true;
            $this->dispatch('open-modal', id: 'receipt-modal');

            Notification::make()
                ->title('Vente enregistrée avec succès !')
                ->body('Montant total : '.FormatHelper::formatFCFA($total))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            Notification::make()
                ->title('Erreur lors de l\'encaissement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->processing = false;
        }
    }

    // ─── Calculations ────────────────────────────────────────────────

    public function getSubtotal(): float
    {
        return (float) array_reduce($this->cart, function ($carry, $item) {
            return $carry + ($item['unitPrice'] * $item['quantity']);
        }, 0);
    }

    public function getDiscountAmount(): float
    {
        $subtotal = $this->getSubtotal();
        if ($this->discountType === 'percentage') {
            return round(($subtotal * min(100, max(0, $this->discountValue))) / 100);
        }

        return min($subtotal, max(0, round($this->discountValue)));
    }

    public function getTotal(): float
    {
        return max(0, $this->getSubtotal() - $this->getDiscountAmount());
    }

    public function getItemsCount(): int
    {
        return array_reduce($this->cart, function ($carry, $item) {
            return $carry + $item['quantity'];
        }, 0);
    }

    // ─── Data Accessors ──────────────────────────────────────────────

    public function getCategories(): Collection
    {
        return ServiceCategory::with(['services' => function ($q) {
            $q->where('status', 'active');
            if ($this->searchQuery) {
                $q->where('name', 'like', '%'.$this->searchQuery.'%');
            }
        }])
            ->orderBy('order')
            ->get();
    }

    public function getPopularServices(): Collection
    {
        // Top 5 popular services
        $popularIds = SaleItem::where('type', 'service')
            ->select('itemId', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('itemId')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->pluck('itemId');

        if ($popularIds->isEmpty()) {
            return Service::where('status', 'active')->limit(5)->get();
        }

        return Service::whereIn('id', $popularIds)
            ->where('status', 'active')
            ->get();
    }

    public function getProducts(): Collection
    {
        $query = Product::where('status', 'active')
            ->with('category');

        if ($this->selectedCategoryId) {
            $query->where('categoryId', $this->selectedCategoryId);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->searchQuery.'%')
                    ->orWhere('reference', 'like', '%'.$this->searchQuery.'%');
            });
        }

        return $query->get();
    }

    public function getClients(): Collection
    {
        $query = Client::query();

        if ($this->clientSearch) {
            $query->where(function ($q) {
                $q->where('firstName', 'like', '%'.$this->clientSearch.'%')
                    ->orWhere('lastName', 'like', '%'.$this->clientSearch.'%')
                    ->orWhere('phone', 'like', '%'.$this->clientSearch.'%');
            });
        }

        return $query->orderBy('firstName')->limit(20)->get();
    }

    public function getBarbers(): Collection
    {
        return Barber::canPerformServices()->get();
    }

    /**
     * Compute real-time availability for a barber.
     */
    public function getBarberAvailability(int $barberId): array
    {
        $now = now();
        $currentTime = $now->hour * 60 + $now->minute;
        $today = $now->toDateString();

        $appointments = Appointment::where('barberId', $barberId)
            ->where('date', $today)
            ->whereIn('status', ['confirmed', 'in_progress', 'pending'])
            ->with('service')
            ->get();

        $isBusy = false;
        $currentService = null;
        $nextAvailableTime = null;

        foreach ($appointments as $appt) {
            $startTimeParts = explode(':', $appt->startTime);
            $endTimeParts = explode(':', $appt->endTime);
            $apptStart = (int) $startTimeParts[0] * 60 + (int) ($startTimeParts[1] ?? 0);
            $apptEnd = (int) $endTimeParts[0] * 60 + (int) ($endTimeParts[1] ?? 0);

            if ($currentTime >= $apptStart && $currentTime < $apptEnd) {
                $isBusy = true;
                $currentService = $appt->service?->name;
                $nextAvailableTime = $apptEnd;
                break;
            }

            if ($apptStart > $currentTime) {
                if ($nextAvailableTime === null || $apptStart < $nextAvailableTime) {
                    $nextAvailableTime = $apptStart;
                }
            }
        }

        if ($isBusy) {
            $minsUntil = $nextAvailableTime !== null ? $nextAvailableTime - $currentTime : null;

            return [
                'status' => 'busy',
                'label' => 'Occupé',
                'color' => 'text-rose-500 bg-rose-500/10 border-rose-500/30',
                'details' => $minsUntil ? '~'.$minsUntil.' min restantes' : 'En prestation',
                'service' => $currentService,
            ];
        }

        if ($nextAvailableTime !== null) {
            $minsUntil = $nextAvailableTime - $currentTime;
            if ($minsUntil <= 30) {
                return [
                    'status' => 'available_soon',
                    'label' => 'Bientôt pris',
                    'color' => 'text-amber-500 bg-amber-500/10 border-amber-500/30',
                    'details' => 'RDV dans '.$minsUntil.' min',
                    'service' => null,
                ];
            }
        }

        return [
            'status' => 'available',
            'label' => 'Libre',
            'color' => 'text-emerald-500 bg-emerald-500/10 border-emerald-500/30',
            'details' => 'Disponible immédiatement',
            'service' => null,
        ];
    }

    public function getLastSale(): ?Sale
    {
        if (! $this->lastSaleId) {
            return null;
        }

        return Sale::with(['client', 'barber', 'items'])->find($this->lastSaleId);
    }
}
