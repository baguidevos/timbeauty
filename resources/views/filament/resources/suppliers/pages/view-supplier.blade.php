<x-filament-panels::page>
    @php
        $stats = $this->getSupplierStats();
        $supplier = $this->record;
        $orders = $this->getOrdersList();
        $products = $this->getProductsList();
    @endphp

    <div class="space-y-6">
        <!-- ─── 1. Header Fiche Fournisseur ──────────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <!-- Background Glow Effect -->
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl dark:bg-amber-500/15"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Avatar & Identité -->
                <div class="flex items-start gap-4 sm:items-center">
                    <div class="relative flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-2xl font-black text-white shadow-lg shadow-amber-500/20 ring-4 ring-amber-500/20">
                        <x-heroicon-o-truck class="h-10 w-10 text-white" />
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white">
                                {{ $supplier->name }}
                            </h1>

                            @if($supplier->isActive())
                                <x-filament::badge color="success" icon="heroicon-m-check-circle" size="sm">
                                    Partenaire Actif
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="danger" icon="heroicon-m-x-circle" size="sm">
                                    Inactif
                                </x-filament::badge>
                            @endif

                            @if($supplier->paymentTerms)
                                <x-filament::badge color="info" icon="heroicon-m-credit-card" size="sm">
                                    {{ $supplier->paymentTerms }}
                                </x-filament::badge>
                            @endif
                        </div>

                        <!-- Coordonnées & Contact rapide -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            @if($supplier->contactName)
                                <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                                    <x-heroicon-m-user class="h-4 w-4 text-amber-500" />
                                    <span>Contact : {{ $supplier->contactName }}</span>
                                </span>
                            @endif

                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="flex items-center gap-1.5 transition hover:text-amber-600 dark:hover:text-amber-400">
                                    <x-heroicon-m-phone class="h-4 w-4 text-gray-400" />
                                    <span>{{ $supplier->phone }}</span>
                                </a>
                            @endif

                            @if($supplier->phone)
                                @php
                                    $cleanWa = preg_replace('/[^0-9]/', '', $supplier->phone);
                                    if(strlen($cleanWa) === 8) $cleanWa = '228'.$cleanWa;
                                @endphp
                                <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="flex items-center gap-1.5 font-medium text-emerald-600 transition hover:underline dark:text-emerald-400">
                                    <x-heroicon-m-chat-bubble-left-ellipsis class="h-4 w-4" />
                                    <span>WhatsApp</span>
                                </a>
                            @endif

                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="flex items-center gap-1.5 transition hover:text-amber-600 dark:hover:text-amber-400">
                                    <x-heroicon-m-envelope class="h-4 w-4 text-gray-400" />
                                    <span>{{ $supplier->email }}</span>
                                </a>
                            @endif

                            @if($supplier->address)
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-m-map-pin class="h-4 w-4 text-gray-400" />
                                    <span>{{ $supplier->address }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('filament.admin.order-management.resources.purchase-orders.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-amber-500/20 transition hover:bg-amber-600">
                        <x-heroicon-m-plus class="h-4 w-4" />
                        <span>Nouvelle commande</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ─── 2. Cartes KPI Statistiques Fournisseur ──────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Achats -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Achats</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['totalSpent']) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        {{ $stats['totalOrders'] }} commande(s) passée(s)
                    </p>
                </div>
            </div>

            <!-- Commandes Reçues -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Commandes Reçues</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-check-circle class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ $stats['receivedOrders'] }}
                    </p>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">
                        {{ $stats['pendingOrders'] > 0 ? $stats['pendingOrders'] . ' en cours/attente' : 'Toutes livrées' }}
                    </p>
                </div>
            </div>

            <!-- Produits Fournis -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Articles Référencés</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                        <x-heroicon-o-cube class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ $stats['totalProducts'] }}
                    </p>
                    <p class="text-[11px] {{ $stats['lowStockProducts'] > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-400' }} mt-0.5">
                        {{ $stats['lowStockProducts'] > 0 ? $stats['lowStockProducts'] . ' en alerte stock bas' : 'Stocks à niveau' }}
                    </p>
                </div>
            </div>

            <!-- Reste à Payer / Solde Dû -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Solde Dû / En cours</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $stats['balanceDue'] > 0 ? 'bg-rose-500/10 text-rose-600' : 'bg-gray-100 text-gray-500 dark:bg-gray-800' }}">
                        <x-heroicon-o-receipt-refund class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black {{ $stats['balanceDue'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-950 dark:text-white' }}">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['balanceDue']) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        Payé : {{ \App\Helpers\FormatHelper::formatFCFA($stats['totalPaid']) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ─── 3. Navigation par Onglets ───────────────────────────────────────── -->
        <div class="space-y-4">
            <div class="flex border-b border-gray-200 dark:border-gray-800 gap-6">
                <button 
                    wire:click="$set('activeTab', 'orders')"
                    class="pb-3 text-sm font-bold transition border-b-2 flex items-center gap-2 {{ $activeTab === 'orders' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <x-heroicon-o-shopping-bag class="h-4 w-4" />
                    <span>Commandes d'achat</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $orders->count() }}
                    </span>
                </button>

                <button 
                    wire:click="$set('activeTab', 'products')"
                    class="pb-3 text-sm font-bold transition border-b-2 flex items-center gap-2 {{ $activeTab === 'products' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <x-heroicon-o-cube class="h-4 w-4" />
                    <span>Catalogue Produits</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        {{ $products->count() }}
                    </span>
                </button>

                <button 
                    wire:click="$set('activeTab', 'notes')"
                    class="pb-3 text-sm font-bold transition border-b-2 flex items-center gap-2 {{ $activeTab === 'notes' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <x-heroicon-o-document-text class="h-4 w-4" />
                    <span>Notes & Conditions</span>
                </button>
            </div>

            <!-- Contenu Onglet 1 : Commandes d'achat -->
            @if($activeTab === 'orders')
                <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-shopping-bag class="h-4 w-4 text-amber-500" />
                            Historique des bons de commande
                        </h3>
                        <span class="text-xs text-gray-400">{{ $orders->count() }} commande(s) trouvée(s)</span>
                    </div>

                    @if($orders->isEmpty())
                        <div class="p-12 text-center">
                            <x-heroicon-o-shopping-cart class="h-12 w-12 text-gray-300 mx-auto dark:text-gray-700 mb-3" />
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Aucune commande enregistrée pour ce fournisseur</p>
                            <p class="text-xs text-gray-400 mt-1">Créez votre premier bon de commande pour approvisionner votre stock.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50/50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-800">
                                    <tr>
                                        <th class="p-3.5">Référence</th>
                                        <th class="p-3.5">Date Commande</th>
                                        <th class="p-3.5">Livraison Prévue</th>
                                        <th class="p-3.5">Articles</th>
                                        <th class="p-3.5">Montant Total</th>
                                        <th class="p-3.5">Statut</th>
                                        <th class="p-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                                    @foreach($orders as $order)
                                        @php
                                            $badgeColor = match($order->status) {
                                                'received' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300',
                                                'ordered' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300',
                                                'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300',
                                                'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300',
                                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                                            };
                                            $badgeLabel = match($order->status) {
                                                'received' => 'Reçue',
                                                'ordered' => 'Envoyée',
                                                'pending' => 'En attente',
                                                'cancelled' => 'Annulée',
                                                default => $order->status,
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                            <td class="p-3.5 font-bold text-amber-600 dark:text-amber-400">
                                                <a href="{{ route('filament.admin.order-management.resources.purchase-orders.view', ['record' => $order->id]) }}" class="hover:underline">
                                                    {{ $order->reference }}
                                                </a>
                                            </td>
                                            <td class="p-3.5 text-gray-600 dark:text-gray-300">
                                                {{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="p-3.5 text-gray-500">
                                                {{ $order->expectedDate ? \Carbon\Carbon::parse($order->expectedDate)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="p-3.5 text-gray-700 dark:text-gray-300">
                                                {{ $order->items->count() }} article(s) ({{ $order->items->sum('quantity') }} unités)
                                            </td>
                                            <td class="p-3.5 font-bold text-gray-950 dark:text-white">
                                                {{ \App\Helpers\FormatHelper::formatFCFA($order->totalAmount) }}
                                            </td>
                                            <td class="p-3.5">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold border {{ $badgeColor }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </td>
                                            <td class="p-3.5 text-right">
                                                <a href="{{ route('filament.admin.order-management.resources.purchase-orders.view', ['record' => $order->id]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 hover:underline">
                                                    <span>Voir détail</span>
                                                    <x-heroicon-m-arrow-right class="h-3.5 w-3.5" />
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Contenu Onglet 2 : Catalogue Produits Fournis -->
            @if($activeTab === 'products')
                <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-cube class="h-4 w-4 text-amber-500" />
                            Articles fournis par {{ $supplier->name }}
                        </h3>
                        <span class="text-xs text-gray-400">{{ $products->count() }} produit(s)</span>
                    </div>

                    @if($products->isEmpty())
                        <div class="p-12 text-center">
                            <x-heroicon-o-cube class="h-12 w-12 text-gray-300 mx-auto dark:text-gray-700 mb-3" />
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Aucun produit associé à ce fournisseur</p>
                            <p class="text-xs text-gray-400 mt-1">Vous pouvez associer des produits à ce fournisseur depuis le module Produits.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50/50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-800">
                                    <tr>
                                        <th class="p-3.5">Produit</th>
                                        <th class="p-3.5">Catégorie</th>
                                        <th class="p-3.5">Prix d'achat</th>
                                        <th class="p-3.5">Prix de vente</th>
                                        <th class="p-3.5">Marge unitaire</th>
                                        <th class="p-3.5">Stock actuel</th>
                                        <th class="p-3.5">Statut Stock</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                                    @foreach($products as $product)
                                        @php
                                            $sellingPrice = (float) ($product->sellingPrice ?? 0);
                                            $purchasePrice = (float) ($product->purchasePrice ?? 0);
                                            $margin = $sellingPrice - $purchasePrice;
                                            $marginPct = $sellingPrice > 0 ? round(($margin / $sellingPrice) * 100) : 0;
                                            $isLow = $product->isLowStock();
                                        @endphp
                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                            <td class="p-3.5">
                                                <div class="flex items-center gap-3">
                                                    @if($product->photo)
                                                        <img src="{{ Storage::url($product->photo) }}" class="h-8 w-8 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="{{ $product->name }}">
                                                    @else
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                                            <x-heroicon-o-cube class="h-4 w-4" />
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="font-bold text-gray-900 dark:text-white">{{ $product->name }}</p>
                                                        <p class="text-[10px] text-gray-400">Réf: {{ $product->reference ?: '—' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-3.5 text-gray-500">
                                                {{ $product->category?->name ?? 'Général' }}
                                            </td>
                                            <td class="p-3.5 font-semibold text-gray-900 dark:text-white">
                                                {{ \App\Helpers\FormatHelper::formatFCFA($purchasePrice) }}
                                            </td>
                                            <td class="p-3.5 font-bold text-gray-950 dark:text-white">
                                                {{ \App\Helpers\FormatHelper::formatFCFA($sellingPrice) }}
                                            </td>
                                            <td class="p-3.5 font-semibold text-emerald-600 dark:text-emerald-400">
                                                +{{ \App\Helpers\FormatHelper::formatFCFA($margin) }} ({{ $marginPct }}%)
                                            </td>
                                            <td class="p-3.5">
                                                <span class="font-bold {{ $isLow ? 'text-rose-600' : 'text-gray-900 dark:text-white' }}">
                                                    {{ $product->stockQuantity }}
                                                </span>
                                                <span class="text-gray-400 text-[10px]"> / Min: {{ $product->minStockLevel }}</span>
                                            </td>
                                            <td class="p-3.5">
                                                @if($isLow)
                                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300">
                                                        <x-heroicon-s-exclamation-triangle class="h-3 w-3 text-rose-600" />
                                                        Stock Bas
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        Optimal
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Contenu Onglet 3 : Notes & Conditions -->
            @if($activeTab === 'notes')
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Conditions Commerciales -->
                        <div class="space-y-4 rounded-xl bg-gray-50/50 p-5 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-heroicon-o-credit-card class="h-4 w-4 text-amber-500" />
                                Conditions Commerciales
                            </h4>
                            <dl class="divide-y divide-gray-200/60 dark:divide-gray-700/60 text-xs">
                                <div class="py-2.5 flex justify-between">
                                    <dt class="text-gray-500">Conditions de paiement</dt>
                                    <dd class="font-bold text-gray-900 dark:text-white">{{ $supplier->paymentTerms ?: 'Non spécifié' }}</dd>
                                </div>
                                <div class="py-2.5 flex justify-between">
                                    <dt class="text-gray-500">Contact Référent</dt>
                                    <dd class="font-bold text-gray-900 dark:text-white">{{ $supplier->contactName ?: '—' }}</dd>
                                </div>
                                <div class="py-2.5 flex justify-between">
                                    <dt class="text-gray-500">Adresse de livraison / Magasin</dt>
                                    <dd class="font-bold text-gray-900 dark:text-white text-right max-w-[220px] truncate">{{ $supplier->address ?: '—' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Notes Internes avec édition réactive -->
                        <div class="space-y-3 rounded-xl bg-gray-50/50 p-5 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <x-heroicon-o-pencil-square class="h-4 w-4 text-amber-500" />
                                    Notes & Historique Négociations
                                </h4>
                                @if(!$isEditingNotes)
                                    <button 
                                        wire:click="$set('isEditingNotes', true)"
                                        class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                        Modifier
                                    </button>
                                @endif
                            </div>

                            @if($isEditingNotes)
                                <div class="space-y-3">
                                    <textarea 
                                        wire:model="supplierNotes" 
                                        rows="4" 
                                        class="w-full rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-900 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" 
                                        placeholder="Saisissez des notes sur ce fournisseur..."></textarea>
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            wire:click="$set('isEditingNotes', false)" 
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                            Annuler
                                        </button>
                                        <button 
                                            wire:click="saveNotes" 
                                            class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600">
                                            Enregistrer
                                        </button>
                                    </div>
                                </div>
                            @else
                                <p class="text-xs text-gray-600 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                                    {{ $supplier->notes ?: 'Aucune note particulière enregistrée pour ce fournisseur.' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
