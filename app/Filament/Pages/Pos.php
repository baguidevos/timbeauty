<?php

namespace App\Filament\Pages;

use App\Helpers\FormatHelper;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Client;
use App\Models\LoyaltyPointTransaction;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionUsage;
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

    public $clientId = null;

    public $appointmentId = null;

    public $barberId = null;

    public $selectedPromotionId = null;

    public string $discountType = 'percentage'; // 'percentage' | 'fixed'

    public $discountValue = 0;

    public string $paymentMethod = 'cash';

    public string $notes = '';

    // Search and filters
    public string $activeTab = 'services'; // 'services' | 'products'

    public string $searchQuery = '';

    public string $clientSearch = '';

    public $selectedCategoryId = null;

    // Modals
    public bool $showQuickClientModal = false;

    public bool $showAppointmentModal = false;

    public string $quickClientFirstName = '';

    public string $quickClientLastName = '';

    public string $quickClientPhone = '';

    public ?string $quickClientGender = 'male';

    public bool $showReceiptModal = false;

    public ?int $lastSaleId = null;

    public bool $processing = false;

    /**
     * Hydrate cart state from request or session.
     */
    public function mount(): void
    {
        if (request()->has('appointment')) {
            $this->loadAppointment((int) request()->get('appointment'));
        } elseif (session()->has('pos_cart')) {
            $saved = session('pos_cart', []);
            $this->cart = $saved['cart'] ?? [];
            $this->clientId = $saved['clientId'] ?? null;
            $this->appointmentId = $saved['appointmentId'] ?? null;
            $this->barberId = $saved['barberId'] ?? null;
            $this->selectedPromotionId = $saved['selectedPromotionId'] ?? null;
            $this->discountType = $saved['discountType'] ?? 'percentage';
            $this->discountValue = $saved['discountValue'] ?? 0;
            $this->paymentMethod = $saved['paymentMethod'] ?? 'cash';
            $this->notes = $saved['notes'] ?? '';
        }

        if (request()->has('client')) {
            $this->clientId = (int) request()->get('client');
            $this->saveCartToSession();
        }
    }

    /**
     * Load an appointment into the POS cart.
     */
    public function loadAppointment(int $appointmentId): void
    {
        $appointment = Appointment::with(['client', 'barber', 'service'])->find($appointmentId);
        if (! $appointment) {
            Notification::make()
                ->title('Rendez-vous introuvable')
                ->danger()
                ->send();

            return;
        }

        $this->appointmentId = $appointment->id;
        $this->clientId = $appointment->clientId;
        $this->barberId = $appointment->barberId;

        // Clear and populate cart with the appointment service
        $this->cart = [];
        if ($appointment->service) {
            $key = 'service_'.$appointment->service->id;
            $this->cart[$key] = [
                'type' => 'service',
                'itemId' => $appointment->service->id,
                'name' => $appointment->service->name,
                'unitPrice' => (float) $appointment->service->price,
                'duration' => $appointment->service->duration,
                'quantity' => 1,
            ];
        }

        if ($appointment->notes) {
            $this->notes = 'RDV: '.$appointment->notes;
        }

        $this->saveCartToSession();
        $this->showAppointmentModal = false;

        $clientName = $appointment->client ? "{$appointment->client->firstName} {$appointment->client->lastName}" : 'Client';
        $serviceName = $appointment->service?->name ?? 'Prestation';

        Notification::make()
            ->title('Rendez-vous chargé au panier')
            ->body("{$clientName} • {$serviceName}")
            ->success()
            ->send();
    }

    /**
     * Unlink current appointment from cart.
     */
    public function unlinkAppointment(): void
    {
        $this->appointmentId = null;
        $this->saveCartToSession();

        Notification::make()
            ->title('Liaison RDV retirée')
            ->info()
            ->send();
    }

    /**
     * Get list of pending appointments for today that are not yet paid.
     */
    public function getPendingAppointmentsProperty(): Collection
    {
        return Appointment::with(['client', 'barber', 'service'])
            ->whereDate('date', now()->toDateString())
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->whereDoesntHave('sales', fn ($q) => $q->where('status', 'completed'))
            ->orderBy('startTime')
            ->get();
    }

    /**
     * Persist current cart state in user session.
     */
    public function saveCartToSession(): void
    {
        session(['pos_cart' => [
            'cart' => $this->cart,
            'clientId' => $this->clientId ? (int) $this->clientId : null,
            'appointmentId' => $this->appointmentId ? (int) $this->appointmentId : null,
            'barberId' => $this->barberId ? (int) $this->barberId : null,
            'selectedPromotionId' => $this->selectedPromotionId ? (int) $this->selectedPromotionId : null,
            'discountType' => $this->discountType,
            'discountValue' => is_numeric($this->discountValue) ? (float) $this->discountValue : 0,
            'paymentMethod' => $this->paymentMethod,
            'notes' => $this->notes,
        ]]);
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['clientId', 'appointmentId', 'barberId', 'selectedPromotionId', 'discountType', 'discountValue', 'paymentMethod', 'notes'])) {
            $this->saveCartToSession();
        }
    }

    public function updatedSelectedPromotionId($value): void
    {
        $this->applyPromotion($value ? (int) $value : null);
    }

    public function applyPromotion(?int $promotionId): void
    {
        if (! $promotionId) {
            $this->selectedPromotionId = null;
            $this->discountValue = 0;
            $this->saveCartToSession();

            return;
        }

        $promotion = Promotion::with('services')->find($promotionId);
        if (! $promotion || ! $promotion->isActive()) {
            $this->selectedPromotionId = null;
            $this->discountValue = 0;
            $this->saveCartToSession();

            Notification::make()
                ->title('Cette promotion n\'est pas active ou est expirée.')
                ->warning()
                ->send();

            return;
        }

        // Check client constraints
        if ($this->clientId) {
            $client = Client::find($this->clientId);
            if ($client) {
                if ($promotion->forLoyalOnly && ! $client->isLoyal) {
                    $this->selectedPromotionId = null;
                    $this->discountValue = 0;
                    $this->saveCartToSession();

                    Notification::make()
                        ->title('Offre réservée exclusivement aux clients avec badge "Fidèle".')
                        ->warning()
                        ->send();

                    return;
                }

                if ($promotion->minVisits > 0 && ($client->totalVisits ?? 0) < $promotion->minVisits) {
                    $this->selectedPromotionId = null;
                    $this->discountValue = 0;
                    $this->saveCartToSession();

                    Notification::make()
                        ->title("Visites insuffisantes pour cette offre ({$client->totalVisits}/{$promotion->minVisits} visites requises).")
                        ->warning()
                        ->send();

                    return;
                }
            }
        } elseif ($promotion->forLoyalOnly || $promotion->minVisits > 0) {
            $this->selectedPromotionId = null;
            $this->discountValue = 0;
            $this->saveCartToSession();

            Notification::make()
                ->title('Veuillez sélectionner un client pour vérifier l\'éligibilité à cette offre.')
                ->warning()
                ->send();

            return;
        }

        $this->selectedPromotionId = $promotion->id;

        $eligibleCategoryIds = $promotion->categories->pluck('id')->toArray();
        $hasEligibleInCart = false;

        foreach ($this->cart as $item) {
            if ($item['type'] === 'service') {
                $categoryId = $item['categoryId'] ?? Service::find($item['itemId'])?->categoryId;
                if (empty($eligibleCategoryIds) || ($categoryId && in_array((int) $categoryId, $eligibleCategoryIds, true))) {
                    $hasEligibleInCart = true;
                    break;
                }
            }
        }

        if (! empty($eligibleCategoryIds) && ! $hasEligibleInCart) {
            $eligibleNames = $promotion->categories->pluck('name')->implode(', ');
            Notification::make()
                ->title('Aucune prestation éligible dans le panier.')
                ->body("Cette offre s'applique uniquement aux catégories : {$eligibleNames}")
                ->warning()
                ->send();
        }

        if ($promotion->type === 'percentage') {
            $this->discountType = 'percentage';
            $this->discountValue = (float) $promotion->value;
        } elseif ($promotion->type === 'fixed') {
            $this->discountType = 'fixed';
            $this->discountValue = (float) $promotion->value;
        } elseif ($promotion->type === 'free_service') {
            $discount = 0;
            foreach ($this->cart as $item) {
                if ($item['type'] === 'service') {
                    $categoryId = $item['categoryId'] ?? Service::find($item['itemId'])?->categoryId;
                    if (empty($eligibleCategoryIds) || ($categoryId && in_array((int) $categoryId, $eligibleCategoryIds, true))) {
                        $discount = max($discount, (float) $item['unitPrice']);
                    }
                }
            }
            $this->discountType = 'fixed';
            $this->discountValue = $discount > 0 ? $discount : 0;
        }

        $this->saveCartToSession();

        $targetText = ! empty($eligibleCategoryIds)
            ? ' (catégories : '.$promotion->categories->pluck('name')->implode(', ').')'
            : ' (sur toutes les catégories de prestations)';

        Notification::make()
            ->title("Promotion « {$promotion->name} » appliquée !")
            ->body("Remise : {$promotion->formatted_value}{$targetText}")
            ->success()
            ->send();
    }

    public function removePromotion(): void
    {
        $this->selectedPromotionId = null;
        $this->discountValue = 0;
        $this->saveCartToSession();

        Notification::make()
            ->title('Promotion retirée')
            ->info()
            ->duration(1500)
            ->send();
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
                    'categoryId' => $service->categoryId,
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
        $this->selectedPromotionId = null;
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
                'appointmentId' => $this->appointmentId ?: null,
                'barberId' => $this->barberId ?: null,
                'subtotal' => $subtotal,
                'discountAmount' => $discountAmount,
                'total' => $total,
                'paymentMethod' => $this->paymentMethod,
                'status' => 'completed',
                'notes' => $this->notes ?: null,
                'promotionId' => $this->selectedPromotionId ?: null,
                'createdBy' => auth()->id(),
            ]);

            // Update linked appointment to completed if necessary
            if ($this->appointmentId) {
                $linkedAppointment = Appointment::find($this->appointmentId);
                if ($linkedAppointment && $linkedAppointment->status !== 'completed') {
                    $linkedAppointment->update(['status' => 'completed']);
                }
            }

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

            // Record promotion usage if a promotion was applied
            if ($this->selectedPromotionId) {
                $appliedPromo = Promotion::find($this->selectedPromotionId);
                if ($appliedPromo) {
                    PromotionUsage::create([
                        'promotionId' => $appliedPromo->id,
                        'clientId' => $sale->clientId,
                        'saleId' => $sale->id,
                    ]);
                    $appliedPromo->increment('currentUsages');
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
            $this->appointmentId = null;
            $this->barberId = null;
            $this->selectedPromotionId = null;
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

    public function getActivePromotionsProperty(): Collection
    {
        $today = now()->toDateString();

        return Promotion::where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('startDate')->orWhere('startDate', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('endDate')->orWhere('endDate', '>=', $today);
            })
            ->where(function ($q) {
                $q->whereNull('maxUsages')->orWhereRaw('currentUsages < maxUsages');
            })
            ->get();
    }

    public function getSelectedPromotionProperty(): ?Promotion
    {
        if (! $this->selectedPromotionId) {
            return null;
        }

        return Promotion::with('services')->find($this->selectedPromotionId);
    }

    // ─── Calculations ────────────────────────────────────────────────

    public function getSubtotal(): float
    {
        return (float) array_reduce($this->cart, function ($carry, $item) {
            return $carry + ($item['unitPrice'] * $item['quantity']);
        }, 0);
    }

    public function getEligibleSubtotalForPromotion(Promotion $promotion): float
    {
        $eligibleCategoryIds = $promotion->categories->pluck('id')->toArray();

        return (float) array_reduce($this->cart, function ($carry, $item) use ($eligibleCategoryIds) {
            if ($item['type'] !== 'service') {
                return $carry;
            }

            // If specific categories defined for promo, only discount services belonging to those categories
            if (! empty($eligibleCategoryIds)) {
                $categoryId = $item['categoryId'] ?? Service::find($item['itemId'])?->categoryId;
                if (! $categoryId || ! in_array((int) $categoryId, $eligibleCategoryIds, true)) {
                    return $carry;
                }
            }

            return $carry + ($item['unitPrice'] * $item['quantity']);
        }, 0);
    }

    public function getDiscountAmount(): float
    {
        $subtotal = $this->getSubtotal();
        $discountVal = is_numeric($this->discountValue) ? (float) $this->discountValue : 0;

        if ($this->selectedPromotionId) {
            $promotion = Promotion::with('categories')->find($this->selectedPromotionId);
            if ($promotion && $promotion->isActive()) {
                $eligibleSubtotal = $this->getEligibleSubtotalForPromotion($promotion);

                if ($eligibleSubtotal <= 0) {
                    return 0;
                }

                if ($promotion->type === 'percentage') {
                    return round(($eligibleSubtotal * min(100, max(0, $discountVal))) / 100);
                }

                if ($promotion->type === 'free_service') {
                    $eligibleCategoryIds = $promotion->categories->pluck('id')->toArray();
                    $freeServicePrice = 0;
                    foreach ($this->cart as $item) {
                        if ($item['type'] === 'service') {
                            $categoryId = $item['categoryId'] ?? Service::find($item['itemId'])?->categoryId;
                            if (empty($eligibleCategoryIds) || ($categoryId && in_array((int) $categoryId, $eligibleCategoryIds, true))) {
                                $freeServicePrice = max($freeServicePrice, (float) $item['unitPrice']);
                            }
                        }
                    }

                    return min($eligibleSubtotal, $freeServicePrice);
                }

                return min($eligibleSubtotal, max(0, round($discountVal)));
            }
        }

        if ($this->discountType === 'percentage') {
            return round(($subtotal * min(100, max(0, $discountVal))) / 100);
        }

        return min($subtotal, max(0, round($discountVal)));
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

    public function getActivePromotionsMap(): array
    {
        $today = now()->toDateString();
        $promotions = Promotion::where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('startDate')->orWhere('startDate', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('endDate')->orWhere('endDate', '>=', $today);
            })
            ->where(function ($q) {
                $q->whereNull('maxUsages')->orWhereColumn('currentUsages', '<', 'maxUsages');
            })
            ->with('categories')
            ->get();

        $map = [
            'byCategory' => [],
            'global' => null,
        ];

        foreach ($promotions as $promo) {
            if ($promo->categories->isEmpty()) {
                if (! $map['global']) {
                    $map['global'] = $promo;
                }
            } else {
                foreach ($promo->categories as $cat) {
                    if (! isset($map['byCategory'][$cat->id])) {
                        $map['byCategory'][$cat->id] = $promo;
                    }
                }
            }
        }

        return $map;
    }

    public function getPromotionForService(Service $service, array $promotionsMap): ?Promotion
    {
        return $promotionsMap['byCategory'][$service->categoryId] ?? $promotionsMap['global'] ?? null;
    }

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
