<x-filament-panels::page>
    @php
        $categories = $this->getCategories();
        $products = $this->getProducts();
        $popularServices = $this->getPopularServices();
        $clients = $this->getClients();
        $barbers = $this->getBarbers();
        $activePromotions = $this->activePromotions;
        $subtotal = $this->getSubtotal();
        $discountAmount = $this->getDiscountAmount();
        $total = $this->getTotal();
        $itemsCount = $this->getItemsCount();
        $lastSale = $this->getLastSale();
    @endphp

    <div 
        x-data="{
            mobileTab: 'catalog',
            init() {
                window.addEventListener('keydown', (e) => {
                    // F2 -> Focus Client Search/Select
                    if (e.key === 'F2') {
                        e.preventDefault();
                        const clientSelect = document.getElementById('pos-client-select');
                        if (clientSelect) {
                            clientSelect.focus();
                        }
                    }
                    // F9 -> Process Sale
                    if (e.key === 'F9') {
                        e.preventDefault();
                        if (@js(count($cart)) > 0 && !@js($processing)) {
                            $wire.processSale();
                        }
                    }
                    // Escape -> Clear Cart (when not focused on text input)
                    if (e.key === 'Escape' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                        if (@js(count($cart)) > 0) {
                            if (confirm('Voulez-vous vraiment vider le panier actuel ?')) {
                                $wire.clearCart();
                            }
                        }
                    }
                });
            }
        }"
        class="space-y-6">
        <!-- ─── 1. En-tête POS & Barre d'état ───────────────────────────── -->
        <x-filament::section compact>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                        <x-heroicon-o-shopping-cart class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                                Point de Vente & Caisse
                            </h1>
                            <x-filament::badge color="success" icon="heroicon-m-check-circle" size="sm">
                                Caisse Active
                            </x-filament::badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Enregistrement rapide des prestations, ventes de produits et encaissements
                        </p>
                    </div>
                </div>

                <!-- Raccourcis et Actions rapides -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="hidden items-center gap-1.5 md:flex">
                        <x-filament::badge color="gray" size="sm">
                            <kbd class="font-mono text-[10px] font-bold">F2</kbd> Client
                        </x-filament::badge>
                        <x-filament::badge color="gray" size="sm">
                            <kbd class="font-mono text-[10px] font-bold">F9</kbd> Encaisser
                        </x-filament::badge>
                        <x-filament::badge color="gray" size="sm">
                            <kbd class="font-mono text-[10px] font-bold">Esc</kbd> Vider
                        </x-filament::badge>
                    </div>

                    @if(count($cart) > 0)
                        <x-filament::button
                            wire:click="clearCart"
                            wire:confirm="Voulez-vous vraiment vider le panier en cours ?"
                            color="danger"
                            icon="heroicon-m-trash"
                            size="sm"
                            outlined
                        >
                            Vider le panier
                        </x-filament::button>
                    @endif

                    <x-filament::button
                        x-on:click="$dispatch('open-modal', { id: 'appointment-modal' })"
                        type="button"
                        color="info"
                        icon="heroicon-m-calendar-days"
                        size="sm"
                    >
                        Importer un RDV ({{ count($this->pendingAppointments) }})
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="route('filament.admin.resources.sales.index')"
                        color="gray"
                        icon="heroicon-m-clock"
                        size="sm"
                    >
                        Historique des ventes
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <!-- ─── 2. Onglets de bascule mobile (Catalogue / Panier) ──────── -->
        <div class="lg:hidden">
            <x-filament::tabs>
                <x-filament::tabs.item
                    :active="true"
                    x-on:click="mobileTab = 'catalog'"
                    icon="heroicon-m-squares-2x2"
                >
                    Catalogue
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="false"
                    x-on:click="mobileTab = 'cart'"
                    icon="heroicon-m-shopping-bag"
                    :badge="$itemsCount > 0 ? (string) $itemsCount : null"
                    badge-color="warning"
                >
                    Panier
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        <!-- ─── 3. Disposition principale 2 Colonnes ──────────────────── -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            <!-- ─── COLONNE GAUCHE: Catalogue (7 colonnes) ─────────────── -->
            <div 
                class="flex flex-col gap-6 lg:col-span-7"
                :class="mobileTab !== 'catalog' ? 'hidden lg:flex' : 'flex'"
            >
                <!-- Barre d'onglets Catalogue & Recherche -->
                <x-filament::section compact>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <x-filament::tabs>
                            <x-filament::tabs.item
                                :active="$activeTab === 'services'"
                                wire:click="$set('activeTab', 'services')"
                                icon="heroicon-m-scissors"
                            >
                                Prestations
                            </x-filament::tabs.item>

                            <x-filament::tabs.item
                                :active="$activeTab === 'products'"
                                wire:click="$set('activeTab', 'products')"
                                icon="heroicon-m-cube"
                            >
                                Produits
                            </x-filament::tabs.item>
                        </x-filament::tabs>

                        <!-- Barre de recherche Filament -->
                        <div class="w-full sm:max-w-xs">
                            <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                                <x-filament::input
                                    wire:model.live.debounce.300ms="searchQuery"
                                    type="text"
                                    placeholder="{{ $activeTab === 'services' ? 'Rechercher une prestation...' : 'Rechercher un produit...' }}"
                                />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </x-filament::section>

                <!-- ─── Zone de défilement Catalogue (Prestations & Produits) ─── -->
                <div class="h-[calc(100vh-260px)] min-h-[450px] overflow-y-auto pr-2 space-y-6">
                    <!-- ─── VUE DES PRESTATIONS ────────────────────────────── -->
                    @if($activeTab === 'services')
                        @php
                            $activePromosMap = $this->getActivePromotionsMap();
                        @endphp
                        <div class="space-y-6">
                            <!-- Bandeau des Prestations Populaires -->
                            @if(!$searchQuery && $popularServices->isNotEmpty())
                                <x-filament::section compact>
                                    <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                        <x-heroicon-m-bolt class="h-4 w-4 text-amber-500" />
                                        Prestations Populaires & Rapides
                                    </div>
                                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-5">
                                        @foreach($popularServices as $popular)
                                            @php
                                                $popularPromo = $this->getPromotionForService($popular, $activePromosMap);
                                            @endphp
                                            <button
                                                wire:click="addToCart('service', {{ $popular->id }})"
                                                type="button"
                                                class="group relative flex flex-col justify-between rounded-xl border {{ $popularPromo ? 'border-amber-400/80 bg-amber-50/60 dark:border-amber-500/50 dark:bg-amber-950/30' : 'border-amber-300/60 bg-amber-50/40 dark:border-amber-500/30 dark:bg-amber-950/20' }} p-2.5 text-left shadow-xs transition-all duration-150 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-100/60 hover:shadow-md active:translate-y-0 active:scale-98 dark:hover:bg-amber-900/40"
                                            >
                                                @if($popularPromo)
                                                    <span 
                                                        title="{{ $popularPromo->name }}" 
                                                        class="absolute -top-2 -right-1.5 inline-flex items-center gap-0.5 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-white px-1.5 py-0.5 text-[9px] font-black shadow-xs ring-1 ring-white dark:ring-gray-900"
                                                    >
                                                        <x-heroicon-m-tag class="h-2.5 w-2.5 stroke-[2.5]" />
                                                        {{ $popularPromo->formatted_value }}
                                                    </span>
                                                @endif

                                                <div class="min-w-0">
                                                    <p class="truncate text-xs font-bold text-gray-900 transition group-hover:text-amber-600 dark:text-white">
                                                        {{ $popular->name }}
                                                    </p>
                                                    <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                                        {{ $popular->duration }} min
                                                    </p>
                                                </div>
                                                <div class="mt-2 flex items-center justify-between">
                                                    <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400">
                                                        {{ \App\Helpers\FormatHelper::formatFCFA($popular->price) }}
                                                    </span>
                                                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-amber-500 text-white transition group-hover:scale-110">
                                                        <x-heroicon-m-plus class="h-3.5 w-3.5 stroke-[2.5]" />
                                                    </span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </x-filament::section>
                            @endif

                            <!-- Prestations groupées par catégorie -->
                            @forelse($categories as $category)
                                @if($category->services->isNotEmpty())
                                    @php
                                        $categoryPromo = $activePromosMap['byCategory'][$category->id] ?? $activePromosMap['global'] ?? null;
                                    @endphp
                                    <x-filament::section>
                                        <x-slot name="heading">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span>{{ $category->name }}</span>
                                                <x-filament::badge color="gray" size="xs">
                                                    {{ $category->services->count() }}
                                                </x-filament::badge>
                                                @if($categoryPromo)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/15 text-amber-700 dark:bg-amber-400/20 dark:text-amber-300 px-2 py-0.5 text-[11px] font-bold ring-1 ring-amber-500/30">
                                                        <x-heroicon-m-sparkles class="h-3 w-3 text-amber-500" />
                                                        Promo active ({{ $categoryPromo->formatted_value }})
                                                    </span>
                                                @endif
                                            </div>
                                        </x-slot>

                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            @foreach($category->services as $service)
                                                @php
                                                    $servicePromo = $this->getPromotionForService($service, $activePromosMap);
                                                @endphp
                                                <button
                                                    wire:click="addToCart('service', {{ $service->id }})"
                                                    type="button"
                                                    class="group relative flex flex-col justify-between rounded-xl border {{ $servicePromo ? 'border-amber-400/70 bg-gradient-to-b from-amber-50/30 to-white dark:from-amber-950/20 dark:to-gray-900 dark:border-amber-500/40 ring-1 ring-amber-400/20' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-3 text-left shadow-xs transition-all duration-150 hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md active:translate-y-0 active:scale-98 dark:hover:border-amber-500"
                                                >
                                                    @if($servicePromo)
                                                        <span 
                                                            title="{{ $servicePromo->name }}" 
                                                            class="absolute -top-2 -right-1.5 inline-flex items-center gap-0.5 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-white px-2 py-0.5 text-[10px] font-black shadow-xs ring-2 ring-white dark:ring-gray-900"
                                                        >
                                                            <x-heroicon-m-tag class="h-2.5 w-2.5 stroke-[2.5]" />
                                                            {{ $servicePromo->formatted_value }}
                                                        </span>
                                                    @endif

                                                    <div>
                                                        <h4 class="text-xs font-bold leading-tight text-gray-900 group-hover:text-amber-600 dark:text-white transition">
                                                            {{ $service->name }}
                                                        </h4>
                                                        @if($service->description)
                                                            <p class="mt-1 line-clamp-1 text-[11px] text-gray-400">
                                                                {{ $service->description }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                                                        <div class="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                            <x-heroicon-m-clock class="h-3 w-3 text-gray-400" />
                                                            {{ $service->duration }} min
                                                        </div>
                                                        <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400">
                                                            {{ \App\Helpers\FormatHelper::formatFCFA($service->price) }}
                                                        </span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </x-filament::section>
                                @endif
                            @empty
                                <x-filament::empty-state
                                    icon="heroicon-o-scissors"
                                    heading="Aucune prestation trouvée"
                                    description="Modifiez vos critères de recherche pour afficher les prestations."
                                />
                            @endforelse
                        </div>
                    @endif

                    <!-- ─── VUE DES PRODUITS ───────────────────────────────── -->
                    @if($activeTab === 'products')
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @forelse($products as $product)
                                    @php
                                        $isOutOfStock = $product->stockQuantity <= 0;
                                        $isLowStock = $product->stockQuantity <= $product->minStockLevel;
                                    @endphp
                                    <button
                                        wire:click="addToCart('product', {{ $product->id }})"
                                        @disabled($isOutOfStock)
                                        type="button"
                                        class="group relative flex flex-col justify-between rounded-xl border {{ $isOutOfStock ? 'opacity-50 cursor-not-allowed border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/40' : 'border-gray-200 bg-white hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500' }} p-3 text-left shadow-xs transition-all duration-150 active:scale-98"
                                    >
                                        <div>
                                            <h4 class="text-xs font-bold leading-tight text-gray-900 group-hover:text-amber-600 dark:text-white transition">
                                                {{ $product->name }}
                                            </h4>
                                            @if($product->reference)
                                                <p class="mt-0.5 font-mono text-[10px] text-gray-400">
                                                    {{ $product->reference }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                                            <!-- Badge de Stock Filament -->
                                            @if($isOutOfStock)
                                                <x-filament::badge color="danger" size="xs">
                                                    Rupture (0)
                                                </x-filament::badge>
                                            @elseif($isLowStock)
                                                <x-filament::badge color="warning" size="xs" class="animate-pulse">
                                                    Stock: {{ $product->stockQuantity }}
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="success" size="xs">
                                                    Stock: {{ $product->stockQuantity }}
                                                </x-filament::badge>
                                            @endif

                                            <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400">
                                                {{ \App\Helpers\FormatHelper::formatFCFA($product->sellingPrice) }}
                                            </span>
                                        </div>
                                    </button>
                                @empty
                                    <div class="col-span-full">
                                        <x-filament::empty-state
                                            icon="heroicon-o-cube"
                                            heading="Aucun produit disponible"
                                            description="Vérifiez les filtres de stock ou modifiez votre recherche."
                                        />
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ─── COLONNE DROITE: Caisse & Panier en cours (5 colonnes) ─ -->
            <div 
                class="flex flex-col gap-4 lg:col-span-5"
                :class="mobileTab !== 'cart' ? 'hidden lg:flex' : 'flex'"
            >
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-m-shopping-bag class="h-5 w-5 text-amber-500" />
                            <span>Vente en cours</span>
                        </div>
                    </x-slot>

                    <x-slot name="headerEnd">
                        @if($itemsCount > 0)
                            <x-filament::badge color="warning" size="sm">
                                {{ $itemsCount }} article(s)
                            </x-filament::badge>
                        @endif
                    </x-slot>

                    <div class="space-y-4">
                        @if($appointmentId)
                            @php
                                $linkedAppt = \App\Models\Appointment::with(['client', 'service', 'barber'])->find($appointmentId);
                            @endphp
                            @if($linkedAppt)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-info-50 dark:bg-info-950/40 border border-info-200 dark:border-info-800/80 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-info-500 text-white shrink-0 shadow-xs">
                                            <x-heroicon-m-calendar-days class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-info-900 dark:text-info-100 flex items-center gap-1.5">
                                                <span>RDV #{{ $linkedAppt->id }}</span>
                                                <span class="font-mono text-[10px] font-medium bg-info-200/60 dark:bg-info-900 px-1.5 py-0.5 rounded text-info-800 dark:text-info-200">{{ substr($linkedAppt->startTime, 0, 5) }}</span>
                                            </div>
                                            <div class="text-[11px] text-info-700 dark:text-info-300 mt-0.5">
                                                {{ $linkedAppt->service?->name ?? 'Prestation' }} • Coiffeur: {{ $linkedAppt->barber?->firstName ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="unlinkAppointment"
                                        class="p-1 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-100/50 dark:hover:bg-rose-950/50 transition-colors"
                                        title="Délier ce rendez-vous"
                                    >
                                        <x-heroicon-m-x-mark class="h-4 w-4" />
                                    </button>
                                </div>
                            @endif
                        @endif

                        @php
                            $hasServicesInCart = $this->hasServicesInCart();
                        @endphp

                        <!-- ─── Sélection Client ────────────────────────── -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    <x-heroicon-m-user class="h-3.5 w-3.5 text-amber-500" />
                                    Client
                                    @if($hasServicesInCart)
                                        <span class="text-rose-500 font-bold" title="Obligatoire pour les prestations">*</span>
                                    @endif
                                </label>
                                <div class="flex items-center gap-2">
                                    @if($hasServicesInCart && ! $clientId)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-200 dark:border-rose-800 animate-pulse">
                                            <x-heroicon-m-exclamation-triangle class="h-3 w-3" />
                                            Requis pour prestation
                                        </span>
                                    @endif
                                    <x-filament::button
                                        x-on:click="$dispatch('open-modal', { id: 'quick-client-modal' })"
                                        type="button"
                                        color="warning"
                                        icon="heroicon-m-user-plus"
                                        size="xs"
                                        outlined
                                    >
                                        Nouveau client
                                    </x-filament::button>
                                </div>
                            </div>

                            <x-filament::input.wrapper :valid="! $errors->has('clientId')">
                                <x-filament::input.select
                                    id="pos-client-select"
                                    wire:model.live="clientId"
                                >
                                    <option value="">👤 {{ $hasServicesInCart ? 'Veuillez sélectionner ou créer un client...' : 'Client sans rendez-vous (Walk-in)' }}</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->getFullName() }} {{ $c->code ? '['.$c->code.'] ' : '' }}({{ $c->phone }})
                                        </option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                            @error('clientId')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1.5 bg-rose-50 dark:bg-rose-950/40 p-2 rounded-lg border border-rose-200 dark:border-rose-800/80">
                                    <x-heroicon-m-exclamation-circle class="h-4 w-4 shrink-0 text-rose-500" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- ─── Sélection Coiffeur ─────────────────────── -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    <x-heroicon-m-scissors class="h-3.5 w-3.5 text-amber-500" />
                                    Coiffeur / Barbier assigné
                                    @if($hasServicesInCart)
                                        <span class="text-rose-500 font-bold" title="Obligatoire pour les prestations">*</span>
                                    @endif
                                </label>
                                @if($hasServicesInCart && ! $barberId)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-200 dark:border-rose-800 animate-pulse">
                                        <x-heroicon-m-exclamation-triangle class="h-3 w-3" />
                                        Requis pour prestation
                                    </span>
                                @endif
                            </div>
                            
                            <x-filament::input.wrapper :valid="! $errors->has('barberId')">
                                <x-filament::input.select wire:model.live="barberId">
                                    <option value="">✂️ {{ $hasServicesInCart ? 'Veuillez sélectionner un coiffeur...' : 'Aucun coiffeur assigné' }}</option>
                                    @foreach($barbers as $barber)
                                        @php
                                            $avail = $this->getBarberAvailability($barber->id);
                                        @endphp
                                        <option value="{{ $barber->id }}">
                                            {{ $barber->getFullName() }} — [{{ $avail['label'] }}]
                                        </option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                            @error('barberId')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1.5 bg-rose-50 dark:bg-rose-950/40 p-2 rounded-lg border border-rose-200 dark:border-rose-800/80">
                                    <x-heroicon-m-exclamation-circle class="h-4 w-4 shrink-0 text-rose-500" />
                                    {{ $message }}
                                </p>
                            @enderror

                            @if($barberId)
                                @php
                                    $activeBarberAvail = $this->getBarberAvailability($barberId);
                                    $badgeColor = match($activeBarberAvail['status']) {
                                        'available' => 'success',
                                        'available_soon' => 'warning',
                                        'busy' => 'danger',
                                        default => 'gray',
                                    };
                                @endphp
                                <div class="mt-1 flex items-center justify-between">
                                    <x-filament::badge :color="$badgeColor" size="sm">
                                        {{ $activeBarberAvail['label'] }} : {{ $activeBarberAvail['details'] }}
                                    </x-filament::badge>
                                    @if($activeBarberAvail['service'])
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">({{ $activeBarberAvail['service'] }})</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- ─── Liste des Articles du Panier ───────────── -->
                        <div class="border-t border-gray-100 pt-3 dark:border-gray-800">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-400">
                                Articles du panier
                            </label>

                            <div class="mt-2 max-h-[260px] space-y-2 overflow-y-auto pr-1">
                                @forelse($cart as $key => $item)
                                    <div class="flex items-center justify-between gap-2 rounded-xl border border-gray-100 bg-gray-50/80 p-2.5 transition hover:border-amber-300 dark:border-gray-800/80 dark:bg-gray-800/40 dark:hover:border-amber-500/50">
                                        <div class="flex min-w-0 flex-1 items-center gap-2.5">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $item['type'] === 'service' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300' }}">
                                                @if($item['type'] === 'service')
                                                    <x-heroicon-m-scissors class="h-4 w-4" />
                                                @else
                                                    <x-heroicon-m-cube class="h-4 w-4" />
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-xs font-bold text-gray-900 dark:text-white">
                                                    {{ $item['name'] }}
                                                </p>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    {{ \App\Helpers\FormatHelper::formatFCFA($item['unitPrice']) }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Boutons de Quantité Filament -->
                                        <div class="flex items-center gap-1">
                                            <x-filament::icon-button
                                                wire:click="updateQuantity('{{ $key }}', -1)"
                                                icon="heroicon-m-minus"
                                                size="xs"
                                                color="gray"
                                                label="Diminuer"
                                            />
                                            <span class="w-6 text-center text-xs font-bold tabular-nums text-gray-900 dark:text-white">
                                                {{ $item['quantity'] }}
                                            </span>
                                            <x-filament::icon-button
                                                wire:click="updateQuantity('{{ $key }}', 1)"
                                                icon="heroicon-m-plus"
                                                size="xs"
                                                color="gray"
                                                label="Augmenter"
                                            />
                                        </div>

                                        <!-- Total Ligne -->
                                        <span class="min-w-[70px] text-right text-xs font-bold tabular-nums text-gray-900 dark:text-white">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($item['unitPrice'] * $item['quantity']) }}
                                        </span>

                                        <!-- Supprimer -->
                                        <x-filament::icon-button
                                            wire:click="removeFromCart('{{ $key }}')"
                                            icon="heroicon-m-trash"
                                            size="xs"
                                            color="danger"
                                            label="Supprimer"
                                        />
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 py-8 text-center dark:border-gray-800">
                                        <x-heroicon-o-shopping-bag class="h-8 w-8 text-gray-400" />
                                        <p class="mt-2 text-xs font-bold text-gray-700 dark:text-gray-300">Votre panier est vide</p>
                                        <p class="text-[11px] text-gray-400">Cliquez sur une prestation ou un produit pour l'ajouter.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- ─── Bloc des Totaux & Remises ────────────────── -->
                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-3.5 dark:bg-amber-500/10 space-y-3">
                            <div class="flex items-center justify-between text-xs font-medium text-gray-600 dark:text-gray-300">
                                <span>Sous-total</span>
                                <span class="font-bold tabular-nums text-gray-900 dark:text-white">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($subtotal) }}
                                </span>
                            </div>

                            <!-- Sélection de Promotion -->
                            <div class="space-y-1.5 pt-1 border-t border-amber-500/20">
                                <div class="flex items-center justify-between text-xs">
                                    <label class="flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-300">
                                        <x-heroicon-m-sparkles class="h-3.5 w-3.5 text-amber-500" />
                                        Promotion / Offre
                                    </label>
                                    @if($selectedPromotionId)
                                        <button
                                            type="button"
                                            wire:click="removePromotion"
                                            class="text-[11px] font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:underline inline-flex items-center gap-0.5"
                                        >
                                            <x-heroicon-m-x-mark class="h-3 w-3" /> Retirer
                                        </button>
                                    @endif
                                </div>
                                <x-filament::input.wrapper size="sm">
                                    <x-filament::input.select
                                        wire:model.live="selectedPromotionId"
                                    >
                                        <option value="">-- Aucune promotion (ou remise libre) --</option>
                                        @foreach($activePromotions as $promo)
                                            <option value="{{ $promo->id }}">
                                                🏷️ {{ $promo->name }} ({{ $promo->formatted_value }})
                                                @if($promo->forLoyalOnly) [⭐ Fidèle] @endif
                                            </option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>

                                @if($selectedPromotion = $this->selectedPromotion)
                                    <div class="text-[11px] text-amber-800 dark:text-amber-300 bg-amber-500/10 p-2 rounded-lg border border-amber-500/20">
                                        <span class="font-medium">
                                            🎯 @if($selectedPromotion->services->count() > 0)
                                                Remise ciblée uniquement sur : <strong>{{ $selectedPromotion->services->pluck('name')->implode(', ') }}</strong>
                                            @else
                                                Remise applicable sur <strong>l'ensemble des prestations</strong>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Remise manuelle / Calculée -->
                            <div class="flex items-center justify-between gap-2 text-xs pt-1 border-t border-amber-500/10">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-m-tag class="h-3.5 w-3.5 text-amber-600" />
                                    <span class="font-medium text-gray-600 dark:text-gray-300">Remise</span>
                                    <x-filament::input.wrapper size="sm" class="w-32">
                                        <x-filament::input.select
                                            wire:model.live="discountType"
                                        >
                                            <option value="percentage">% Pourcentage</option>
                                            <option value="fixed">Montant Fixe</option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </div>
                                <div class="w-24">
                                    <x-filament::input.wrapper size="sm">
                                        <x-filament::input
                                            wire:model.live.debounce.300ms="discountValue"
                                            type="number"
                                            min="0"
                                            max="{{ $discountType === 'percentage' ? 100 : 9999999 }}"
                                            placeholder="0"
                                            class="text-right font-bold"
                                        />
                                    </x-filament::input.wrapper>
                                </div>
                            </div>

                            @if($discountAmount > 0)
                                <div class="flex items-center justify-between text-xs font-semibold text-rose-600 dark:text-rose-400">
                                    <span>Déduction remise</span>
                                    <span>- {{ \App\Helpers\FormatHelper::formatFCFA($discountAmount) }}</span>
                                </div>
                            @endif

                            <div class="border-t border-amber-500/20 pt-2 flex items-center justify-between">
                                <span class="text-sm font-extrabold text-gray-950 dark:text-white">TOTAL À PAYER</span>
                                <span class="text-xl font-black text-amber-600 dark:text-amber-400 tabular-nums">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($total) }}
                                </span>
                            </div>
                        </div>

                        <!-- ─── Modes de Règlement ──────────────────────── -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Mode de règlement
                            </label>
                            <div class="grid grid-cols-5 gap-1.5">
                                @php
                                    $methods = [
                                        ['id' => 'cash', 'label' => 'Espèces'],
                                        ['id' => 'tmoney', 'label' => 'TMoney'],
                                        ['id' => 'flooz', 'label' => 'Flooz'],
                                        ['id' => 'card', 'label' => 'Carte'],
                                        ['id' => 'transfer', 'label' => 'Virement'],
                                    ];
                                @endphp
                                @foreach($methods as $method)
                                    <button
                                        wire:click="$set('paymentMethod', '{{ $method['id'] }}')"
                                        type="button"
                                        class="flex flex-col items-center justify-center rounded-xl border p-2 text-center transition {{ $paymentMethod === $method['id'] ? 'border-amber-500 bg-amber-500/10 text-amber-700 dark:text-amber-400 font-bold shadow-xs' : 'border-gray-200 bg-white text-gray-600 hover:border-amber-300 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400 font-medium' }}"
                                    >
                                        <span class="text-[11px] leading-tight">{{ $method['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- ─── Erreurs de validation POS ───────────────── -->
                        @if($errors->any())
                            <div class="rounded-xl border border-rose-200 bg-rose-50/80 p-3 text-xs text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 space-y-1">
                                <div class="font-bold flex items-center gap-1.5">
                                    <x-heroicon-m-exclamation-triangle class="h-4 w-4 text-rose-500 shrink-0" />
                                    <span>Veuillez corriger les points suivants :</span>
                                </div>
                                <ul class="list-disc list-inside space-y-0.5 text-[11px] pl-1 font-medium">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- ─── Bouton d'Encaissement Filament ──────────── -->
                        <x-filament::button
                            wire:click="processSale"
                            wire:loading.attr="disabled"
                            :disabled="empty($cart) || $processing"
                            color="warning"
                            size="xl"
                            icon="heroicon-m-check-circle"
                            class="w-full shadow-lg shadow-amber-500/25"
                        >
                            ENCAISSER {{ $total > 0 ? \App\Helpers\FormatHelper::formatFCFA($total) : '' }}
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        </div>

        <!-- ─── 4. Modal Création Rapide de Client Filament ────────────── -->
        <x-filament::modal
            id="quick-client-modal"
            width="md"
            icon="heroicon-o-user-plus"
            icon-color="warning"
        >
            <x-slot name="heading">
                Nouveau Client Express (Walk-in)
            </x-slot>

            <x-slot name="description">
                Enregistrez rapidement les coordonnées du client et associez-le au panier.
            </x-slot>

            <form wire:submit="createQuickClient" id="quickClientForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Prénom <span class="text-rose-500">*</span>
                        </label>
                        <x-filament::input.wrapper :valid="! $errors->has('quickClientFirstName')">
                            <x-filament::input
                                wire:model="quickClientFirstName"
                                type="text"
                                required
                                placeholder="ex: Yao"
                            />
                        </x-filament::input.wrapper>
                        @error('quickClientFirstName') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Nom <span class="text-rose-500">*</span>
                        </label>
                        <x-filament::input.wrapper :valid="! $errors->has('quickClientLastName')">
                            <x-filament::input
                                wire:model="quickClientLastName"
                                type="text"
                                required
                                placeholder="ex: Mensah"
                            />
                        </x-filament::input.wrapper>
                        @error('quickClientLastName') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Numéro de téléphone <span class="text-xs text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <x-filament::input.wrapper :valid="! $errors->has('quickClientPhone')" prefix-icon="heroicon-m-phone">
                        <x-filament::input
                            wire:model="quickClientPhone"
                            type="tel"
                            placeholder="ex: +228 90 12 34 56"
                        />
                    </x-filament::input.wrapper>
                    @error('quickClientPhone') <span class="text-[10px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Genre</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-800 p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                            <input type="radio" wire:model="quickClientGender" value="male" class="text-amber-500 focus:ring-amber-500">
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">Homme</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-800 p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                            <input type="radio" wire:model="quickClientGender" value="female" class="text-amber-500 focus:ring-amber-500">
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">Femme</span>
                        </label>
                    </div>
                </div>
            </form>

            <x-slot name="footerActions">
                <x-filament::button
                    x-on:click="$dispatch('close-modal', { id: 'quick-client-modal' })"
                    type="button"
                    color="gray"
                >
                    Annuler
                </x-filament::button>

                <x-filament::button
                    wire:click="createQuickClient"
                    type="submit"
                    form="quickClientForm"
                    color="warning"
                    icon="heroicon-m-check"
                    wire:loading.attr="disabled"
                >
                    Enregistrer et sélectionner
                </x-filament::button>
            </x-slot>
        </x-filament::modal>

        <!-- ─── Modal Importer un Rendez-vous du jour ───────────────────── -->
        <x-filament::modal
            id="appointment-modal"
            width="2xl"
            icon="heroicon-o-calendar-days"
            icon-color="info"
        >
            <x-slot name="heading">
                Rendez-vous du jour à encaisser
            </x-slot>

            <x-slot name="description">
                Sélectionnez un rendez-vous effectué aujourd'hui pour charger automatiquement le client, la prestation et le coiffeur au panier.
            </x-slot>

            @php
                $pendingAppts = $this->pendingAppointments;
            @endphp

            @if($pendingAppts->isEmpty())
                <div class="py-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400">
                        <x-heroicon-o-calendar class="h-6 w-6" />
                    </div>
                    <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white">Aucun rendez-vous en attente</h3>
                    <p class="mt-1 text-xs text-gray-500">Tous les rendez-vous du jour ont été réglés ou aucun n'est prévu.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-96 overflow-y-auto pr-1">
                    @foreach($pendingAppts as $appt)
                        <div class="py-3 flex items-center justify-between gap-3 hover:bg-gray-50 dark:hover:bg-gray-800/40 p-2.5 rounded-xl transition-colors">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info-100 dark:bg-info-950/60 text-info-600 dark:text-info-400 font-mono font-bold text-xs">
                                    {{ substr($appt->startTime, 0, 5) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-sm text-gray-900 dark:text-white truncate">
                                            {{ $appt->client ? "{$appt->client->firstName} {$appt->client->lastName}" : 'Client anonyme' }}
                                        </p>
                                        @php
                                            $statusColor = match($appt->status) {
                                                'completed' => 'success',
                                                'in_progress' => 'info',
                                                'confirmed' => 'primary',
                                                default => 'warning'
                                            };
                                            $statusLabel = match($appt->status) {
                                                'completed' => 'Terminé',
                                                'in_progress' => 'En cours',
                                                'confirmed' => 'Confirmé',
                                                default => 'En attente'
                                            };
                                        @endphp
                                        <x-filament::badge :color="$statusColor" size="xs">
                                            {{ $statusLabel }}
                                        </x-filament::badge>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Prestation : <span class="font-medium text-gray-700 dark:text-gray-300">{{ $appt->service?->name ?? '—' }}</span>
                                        • Coiffeur : <span class="font-medium text-gray-700 dark:text-gray-300">{{ $appt->barber ? "{$appt->barber->firstName} {$appt->barber->lastName}" : '—' }}</span>
                                    </p>
                                    @if($appt->client?->phone)
                                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">
                                            📞 {{ $appt->client->phone }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-right">
                                    <div class="text-sm font-extrabold text-amber-600 dark:text-amber-400">
                                        {{ $appt->service ? \App\Helpers\FormatHelper::formatFCFA($appt->service->price) : '0 FCFA' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        {{ $appt->service?->duration ?? 0 }} min
                                    </div>
                                </div>

                                <x-filament::button
                                    wire:click="loadAppointment({{ $appt->id }})"
                                    x-on:click="$dispatch('close-modal', { id: 'appointment-modal' })"
                                    type="button"
                                    size="sm"
                                    color="warning"
                                    icon="heroicon-m-arrow-right-circle"
                                >
                                    Charger
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <x-slot name="footerActions">
                <x-filament::button
                    x-on:click="$dispatch('close-modal', { id: 'appointment-modal' })"
                    type="button"
                    color="gray"
                >
                    Fermer
                </x-filament::button>
            </x-slot>
        </x-filament::modal>

        <!-- ─── 5. Modal Ticket de Caisse & Impression Thermique ────────── -->
        <x-filament::modal
            id="receipt-modal"
            width="lg"
            icon="heroicon-o-check-circle"
            icon-color="success"
        >
            <x-slot name="heading">
                Vente {{ $lastSale ? '#'.$lastSale->id : '' }} enregistrée !
            </x-slot>

            <x-slot name="description">
                Ticket de caisse prêt pour remise au client ou impression thermique.
            </x-slot>

            @if($lastSale)
                <!-- Prévisualisation du Ticket Thermique -->
                <div class="max-h-[380px] overflow-y-auto rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 font-mono text-xs text-gray-900 shadow-inner dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                    <div id="thermal-receipt-content" class="space-y-2">
                        <div class="text-center">
                            <p class="font-bold text-sm">{{ \App\Models\Setting::get('shop_name', 'BarberShop Pro') }}</p>
                            <p class="text-[10px] text-gray-500">{{ \App\Models\Setting::get('shop_address', 'Lomé, Togo') }}</p>
                            <p class="text-[10px] text-gray-500">Tél: {{ \App\Models\Setting::get('shop_phone', '+228 90 00 00 00') }}</p>
                        </div>

                        <div class="divider"></div>

                        <div class="flex justify-between text-[11px]">
                            <span>Ticket N°: #{{ $lastSale->id }}</span>
                            <span>{{ $lastSale->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-[11px]">
                            <span>Client: {{ $lastSale->client ? $lastSale->client->getFullName() : 'Client sans RDV' }}</span>
                        </div>
                        @if($lastSale->barber)
                            <div class="text-[11px]">
                                <span>Coiffeur: {{ $lastSale->barber->getFullName() }}</span>
                            </div>
                        @endif

                        <div class="divider"></div>

                        <!-- Articles -->
                        <div class="space-y-1">
                            @foreach($lastSale->items as $item)
                                <div class="flex justify-between text-[11px]">
                                    <span>{{ $item->name }} x{{ $item->quantity }}</span>
                                    <span>{{ \App\Helpers\FormatHelper::formatFCFA($item->total) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="divider"></div>

                        <div class="space-y-0.5 text-[11px]">
                            <div class="flex justify-between">
                                <span>Sous-total</span>
                                <span>{{ \App\Helpers\FormatHelper::formatFCFA($lastSale->subtotal) }}</span>
                            </div>
                            @if($lastSale->discountAmount > 0)
                                <div class="flex justify-between text-rose-600 font-bold">
                                    <span>Remise</span>
                                    <span>- {{ \App\Helpers\FormatHelper::formatFCFA($lastSale->discountAmount) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between font-bold text-sm pt-1">
                                <span>TOTAL</span>
                                <span>{{ \App\Helpers\FormatHelper::formatFCFA($lastSale->total) }}</span>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="text-center text-[10px] text-gray-500 space-y-1">
                            <p>Règlement : {{ strtoupper($lastSale->paymentMethod) }}</p>
                            <p class="font-bold">{{ \App\Models\Setting::get('receipt_footer', 'Merci de votre visite et à très bientôt !') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <x-slot name="footerActions">
                <x-filament::button
                    x-on:click="$dispatch('close-modal', { id: 'receipt-modal' })"
                    type="button"
                    color="gray"
                >
                    Fermer
                </x-filament::button>

                <x-filament::button
                    x-on:click="window.printThermal('58mm')"
                    type="button"
                    color="warning"
                    icon="heroicon-m-printer"
                    outlined
                >
                    Ticket 58mm
                </x-filament::button>

                <x-filament::button
                    x-on:click="window.printThermal('80mm')"
                    type="button"
                    color="warning"
                    icon="heroicon-m-printer"
                >
                    Ticket 80mm
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </div>

    @script
    <script>
        window.printThermal = function(width = '58mm') {
            const printContent = document.getElementById('thermal-receipt-content');
            if (!printContent) return;

            const printWindow = window.open('', '_blank', `width=${width === '58mm' ? 320 : 420},height=600`);
            if (!printWindow) {
                alert("Veuillez autoriser les fenêtres pop-up pour l'impression du reçu.");
                return;
            }

            printWindow.document.open();
            printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Caisse</title>
    <style>
        @page { margin: 0; size: ${width} auto; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: ${width === '58mm' ? '54mm' : '76mm'};
            margin: 0 auto;
            padding: 8px 4px;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .double-divider { border-top: 2px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; }
        .item-row { margin: 4px 0; }
        .total-lg { font-size: 14px; font-weight: bold; margin: 4px 0; }
        @media print {
            body { width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    ${printContent.innerHTML}
</body>
</html>`);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                setTimeout(() => printWindow.close(), 500);
            }, 250);
        };
    </script>
    @endscript
</x-filament-panels::page>
