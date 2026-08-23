<x-filament-panels::page>
    @php
        $stats = $this->getOrderStats();
        $order = $this->record;
        $items = $this->getItemsList();
        $supplier = $stats['supplier'];

        $stepIndex = match($order->status) {
            'pending' => 1,
            'ordered' => 2,
            'received' => 3,
            'cancelled' => 0,
            default => 1,
        };
    @endphp

    <div class="space-y-6 print:hidden">
        <!-- ─── 1. Header Bon de Commande Pro ─────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl dark:bg-amber-500/15"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Identité Commande -->
                <div class="flex items-start gap-4 sm:items-center">
                    <div class="relative flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-2xl font-black text-white shadow-lg shadow-amber-500/20 ring-4 ring-amber-500/20">
                        <x-heroicon-o-shopping-bag class="h-10 w-10 text-white" />
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white">
                                {{ $order->reference }}
                            </h1>

                            @php
                                $badgeColor = match($order->status) {
                                    'received' => 'success',
                                    'partially_received' => 'warning',
                                    'ordered' => 'info',
                                    'pending' => 'warning',
                                    'cancelled' => 'danger',
                                    default => 'gray',
                                };
                                $badgeIcon = match($order->status) {
                                    'received' => 'heroicon-m-check-circle',
                                    'partially_received' => 'heroicon-m-cube',
                                    'ordered' => 'heroicon-m-paper-airplane',
                                    'pending' => 'heroicon-m-clock',
                                    'cancelled' => 'heroicon-m-x-circle',
                                    default => 'heroicon-m-information-circle',
                                };
                                $badgeLabel = match($order->status) {
                                    'received' => 'Reçue & En stock',
                                    'partially_received' => 'Partiellement reçue',
                                    'ordered' => 'Envoyée au fournisseur',
                                    'pending' => 'En attente d\'envoi',
                                    'cancelled' => 'Annulée',
                                    default => $order->status,
                                };
                            @endphp

                            <x-filament::badge :color="$badgeColor" :icon="$badgeIcon" size="sm">
                                {{ $badgeLabel }}
                            </x-filament::badge>
                        </div>

                        <!-- Informations Fournisseur & Dates -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            @if($supplier)
                                <a href="{{ route('filament.admin.order-management.resources.suppliers.view', ['record' => $supplier->id]) }}" class="flex items-center gap-1.5 font-bold text-amber-600 hover:underline dark:text-amber-400">
                                    <x-heroicon-m-truck class="h-4 w-4" />
                                    <span>Fournisseur : {{ $supplier->name }}</span>
                                </a>
                            @endif

                            <span class="flex items-center gap-1.5">
                                <x-heroicon-m-calendar class="h-4 w-4 text-gray-400" />
                                <span>Date : {{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d/m/Y') : '—' }}</span>
                            </span>

                            @if($order->expectedDate)
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-m-clock class="h-4 w-4 text-gray-400" />
                                    <span>Livraison prévue : {{ \Carbon\Carbon::parse($order->expectedDate)->format('d/m/Y') }}</span>
                                </span>
                            @endif

                            @if($order->receivedDate)
                                <span class="flex items-center gap-1.5 font-medium text-emerald-600 dark:text-emerald-400">
                                    <x-heroicon-m-check-badge class="h-4 w-4" />
                                    <span>Reçue le : {{ \Carbon\Carbon::parse($order->receivedDate)->format('d/m/Y') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action rapide -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button 
                        type="button"
                        onclick="window.print()" 
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                        <x-heroicon-o-printer class="h-4 w-4 text-gray-500" />
                        <span>Imprimer le Bon de Commande (A4)</span>
                    </button>
                </div>
            </div>

            <!-- ─── Stepper Workflow Visuel ───────────────────────────────────── -->
            @if($order->status !== 'cancelled')
                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Étape 1 -->
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $stepIndex >= 1 ? 'bg-amber-50/70 border border-amber-200 dark:bg-amber-950/40 dark:border-amber-900/60' : 'bg-gray-50 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800' }}">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $stepIndex >= 1 ? 'bg-amber-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-gray-700' }} text-xs">
                                1
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Création & Attente</p>
                                <p class="text-[10px] text-gray-500">Bon de commande préparé</p>
                            </div>
                        </div>

                        <!-- Étape 2 -->
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $stepIndex >= 2 ? 'bg-sky-50/70 border border-sky-200 dark:bg-sky-950/40 dark:border-sky-900/60' : 'bg-gray-50 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800' }}">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $stepIndex >= 2 ? 'bg-sky-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-gray-700' }} text-xs">
                                2
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Envoyée au Fournisseur</p>
                                <p class="text-[10px] text-gray-500">En cours d'expédition</p>
                            </div>
                        </div>

                        <!-- Étape 3 -->
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $stepIndex >= 3 ? ($order->status === 'partially_received' ? 'bg-amber-50/70 border border-amber-200 dark:bg-amber-950/40 dark:border-amber-900/60' : 'bg-emerald-50/70 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-900/60') : 'bg-gray-50 border border-gray-100 dark:bg-gray-800/40 dark:border-gray-800' }}">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $stepIndex >= 3 ? ($order->status === 'partially_received' ? 'bg-amber-500 text-white font-bold' : 'bg-emerald-500 text-white font-bold') : 'bg-gray-200 text-gray-600 dark:bg-gray-700' }} text-xs">
                                3
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">
                                    {{ $order->status === 'partially_received' ? 'Partiellement Reçue' : 'Reçue & En Stock' }}
                                </p>
                                <p class="text-[10px] text-gray-500">
                                    {{ $order->status === 'partially_received' ? 'En attente du reliquat fournisseur' : 'Stock automatiquement incrémenté' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ─── 2. Cartes KPI Financières & Volumes ────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Commande -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Commande</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['totalAmount']) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        {{ $stats['totalItems'] }} référence(s) d'articles
                    </p>
                </div>
            </div>

            <!-- Volume / Pièces Commandées -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Quantité Totale</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400">
                        <x-heroicon-o-cube class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ $stats['totalQuantity'] }} pièces
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        Reçu : {{ $stats['totalReceivedQuantity'] }} / {{ $stats['totalQuantity'] }}
                    </p>
                </div>
            </div>

            <!-- Montant Réglé -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Montant Payé</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-check-circle class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['paidAmount']) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        Acompte / règlement fournisseur
                    </p>
                </div>
            </div>

            <!-- Reste à Payer -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Solde Restant</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $stats['remainingAmount'] > 0 ? 'bg-rose-500/10 text-rose-600' : 'bg-gray-100 text-gray-500 dark:bg-gray-800' }}">
                        <x-heroicon-o-credit-card class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black {{ $stats['remainingAmount'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-950 dark:text-white' }}">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['remainingAmount']) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        {{ $stats['remainingAmount'] > 0 ? 'À régler à réception' : 'Totalement soldé' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ─── 3. Tableau Détaillé des Articles Commandés ──────────────────────── -->
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-shopping-cart class="h-4 w-4 text-amber-500" />
                    Détail des articles commandés
                </h3>
                <span class="text-xs text-gray-400">{{ $items->count() }} ligne(s) d'articles</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Article</th>
                            <th class="p-3.5">Catégorie</th>
                            <th class="p-3.5 text-center">Qté Commandée</th>
                            <th class="p-3.5 text-center">Qté Reçue</th>
                            <th class="p-3.5 text-right">Prix Unitaire</th>
                            <th class="p-3.5 text-right">Total Ligne</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @foreach($items as $item)
                            @php
                                $product = $item->product;
                                $isFullyReceived = $item->receivedQuantity >= $item->quantity;
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                                <td class="p-3.5">
                                    <div class="flex items-center gap-3">
                                        @if($product && $product->image)
                                            <img src="{{ Storage::url($product->image) }}" class="h-9 w-9 rounded-lg object-cover border border-gray-200 dark:border-gray-700" alt="{{ $item->productName }}">
                                        @else
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                                <x-heroicon-o-cube class="h-4 w-4" />
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->productName }}</p>
                                            @if($product)
                                                <p class="text-[10px] text-gray-400">Stock actuel en rayon : <span class="font-bold {{ $product->isLowStock() ? 'text-rose-500' : 'text-gray-600 dark:text-gray-300' }}">{{ $product->stockQuantity }}</span></p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5 text-gray-500">
                                    {{ $product?->category?->name ?? 'Général' }}
                                </td>
                                <td class="p-3.5 text-center font-bold text-gray-900 dark:text-white">
                                    {{ $item->quantity }}
                                </td>
                                <td class="p-3.5 text-center">
                                    @if(in_array($order->status, ['received', 'partially_received']))
                                        @if($item->receivedQuantity >= $item->quantity)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                <x-heroicon-s-check-circle class="h-3 w-3 text-emerald-600" />
                                                {{ $item->receivedQuantity }} reçus (Complet)
                                            </span>
                                        @elseif($item->receivedQuantity > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300">
                                                <x-heroicon-s-exclamation-triangle class="h-3 w-3 text-amber-600" />
                                                {{ $item->receivedQuantity }}/{{ $item->quantity }} reçus (-{{ $item->quantity - $item->receivedQuantity }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300">
                                                <x-heroicon-s-x-circle class="h-3 w-3 text-rose-600" />
                                                0/{{ $item->quantity }} reçu (Manquant)
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 font-mono">—</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right font-semibold text-gray-900 dark:text-white">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($item->unitPrice) }}
                                </td>
                                <td class="p-3.5 text-right font-bold text-gray-950 dark:text-white">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($item->totalPrice) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <!-- Footer Total -->
                    <tfoot class="bg-gray-50/80 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 font-bold text-xs">
                        <tr>
                            <td colspan="4" class="p-3.5 text-right text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Total Général de la Commande :
                            </td>
                            <td colspan="2" class="p-3.5 text-right text-base text-amber-600 dark:text-amber-400 font-black">
                                {{ \App\Helpers\FormatHelper::formatFCFA($stats['totalAmount']) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ─── 4. Notes & Informations Complémentaires ───────────────────────── -->
        @if($order->notes)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <x-heroicon-o-document-text class="h-4 w-4 text-amber-500" />
                    Instructions / Notes Particulières
                </h4>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $order->notes }}
                </p>
            </div>
        @endif
    </div>

    <!-- ─── 5. Document A4 Imprimable : Bon de Commande Fournisseur Officiel ───── -->
    @php
        $shopName = \App\Models\Setting::get('shop_name', 'TimBeauty');
        $shopPhone = \App\Models\Setting::get('shop_phone', '+228 90 00 00 00');
        $shopAddress = \App\Models\Setting::get('shop_address', 'Lomé, Togo');
        $shopEmail = \App\Models\Setting::get('shop_email', 'contact@timbeauty.tg');
    @endphp

    <div id="purchase-order-printable" class="hidden print:block p-8 bg-white text-gray-900 max-w-4xl mx-auto text-xs leading-normal">
        <!-- En-tête : Salon & Titre Bon de Commande -->
        <div class="flex items-start justify-between border-b-2 border-gray-900 pb-6 mb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black tracking-tight text-gray-950 uppercase">{{ $shopName }}</span>
                </div>
                <p class="text-gray-600 text-xs mt-1 font-medium">Salon de Coiffure & Soins Professionnels</p>
                <p class="text-gray-500 text-[11px] mt-0.5">{{ $shopAddress }} • Tél : {{ $shopPhone }}</p>
                <p class="text-gray-500 text-[11px]">Email : {{ $shopEmail }}</p>
            </div>

            <div class="text-right">
                <span class="inline-block bg-gray-900 text-white font-black text-sm px-3 py-1 rounded uppercase tracking-wider mb-1.5">
                    BON DE COMMANDE
                </span>
                <p class="text-base font-black text-gray-900 font-mono tracking-tight">{{ $order->reference }}</p>
                <p class="text-gray-600 text-xs mt-1">Date d'émission : <span class="font-bold text-gray-900">{{ $order->orderDate ? \Carbon\Carbon::parse($order->orderDate)->format('d/m/Y') : now()->format('d/m/Y') }}</span></p>
                @if($order->expectedDate)
                    <p class="text-gray-600 text-xs">Livraison souhaitée : <span class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($order->expectedDate)->format('d/m/Y') }}</span></p>
                @endif
                <p class="text-gray-600 text-xs">Statut : <span class="font-bold uppercase">{{ $order->status === 'received' ? 'Reçue' : ($order->status === 'ordered' ? 'Commandée' : 'En attente') }}</span></p>
            </div>
        </div>

        <!-- Encadrés Émetteur / Fournisseur -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- Bloc Émetteur (Acheteur) -->
            <div class="border border-gray-300 rounded-lg p-3.5 bg-gray-50/50">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 border-b border-gray-200 pb-1">
                    Émis Par (Acheteur) :
                </p>
                <p class="font-bold text-gray-900 text-sm">{{ $shopName }}</p>
                <p class="text-gray-700 text-xs mt-0.5">Responsable : <span class="font-semibold">{{ $order->creator?->name ?? 'Direction Achats' }}</span></p>
                <p class="text-gray-600 text-xs">Adresse de livraison : {{ $shopAddress }}</p>
                <p class="text-gray-600 text-xs">Contact réception : {{ $shopPhone }}</p>
            </div>

            <!-- Bloc Fournisseur (Vendeur) -->
            <div class="border border-gray-300 rounded-lg p-3.5 bg-gray-50/50">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 border-b border-gray-200 pb-1">
                    Fournisseur (Destinataire) :
                </p>
                @if($supplier)
                    <p class="font-bold text-gray-900 text-sm">{{ $supplier->name }}</p>
                    @if($supplier->contactPerson)
                        <p class="text-gray-700 text-xs mt-0.5">Attn : <span class="font-semibold">{{ $supplier->contactPerson }}</span></p>
                    @endif
                    <p class="text-gray-600 text-xs">Tél : {{ $supplier->phone ?: 'Non renseigné' }}</p>
                    @if($supplier->email)
                        <p class="text-gray-600 text-xs">Email : {{ $supplier->email }}</p>
                    @endif
                    @if($supplier->address)
                        <p class="text-gray-600 text-xs">Adresse : {{ $supplier->address }}</p>
                    @endif
                @else
                    <p class="font-bold text-gray-700 italic">Fournisseur non spécifié</p>
                @endif
            </div>
        </div>

        <!-- Tableau des Articles Commandés -->
        <div class="mb-6">
            <table class="w-full border-collapse border border-gray-300 text-xs">
                <thead>
                    <tr class="bg-gray-100 text-gray-800 font-bold uppercase text-[10px] border-b border-gray-300">
                        <th class="border border-gray-300 p-2 text-center w-10">N°</th>
                        <th class="border border-gray-300 p-2 text-left">Désignation de l'Article</th>
                        <th class="border border-gray-300 p-2 text-center w-24">Quantité</th>
                        <th class="border border-gray-300 p-2 text-right w-28">Prix Unitaire</th>
                        <th class="border border-gray-300 p-2 text-right w-32">Total (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr class="border-b border-gray-200 {{ $index % 2 === 1 ? 'bg-gray-50/40' : '' }}">
                            <td class="border border-gray-300 p-2 text-center font-mono text-gray-500">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 p-2 font-bold text-gray-900">
                                {{ $item->productName }}
                                @if($item->product && $item->product->category)
                                    <span class="text-[10px] font-normal text-gray-500 block">({{ $item->product->category->name }})</span>
                                @endif
                            </td>
                            <td class="border border-gray-300 p-2 text-center font-bold text-gray-900 text-sm">
                                {{ $item->quantity }}
                            </td>
                            <td class="border border-gray-300 p-2 text-right font-medium text-gray-800">
                                {{ number_format($item->unitPrice, 0, ',', ' ') }}
                            </td>
                            <td class="border border-gray-300 p-2 text-right font-bold text-gray-950">
                                {{ number_format($item->totalPrice, 0, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold">
                        <td colspan="4" class="border border-gray-300 p-2 text-right uppercase tracking-wider text-gray-700">Total Général Commande :</td>
                        <td class="border border-gray-300 p-2 text-right font-black text-sm text-gray-950">
                            {{ number_format($stats['totalAmount'], 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @if($stats['paidAmount'] > 0)
                        <tr class="font-medium text-gray-700">
                            <td colspan="4" class="border border-gray-300 p-2 text-right">Acompte versé / Déjà payé :</td>
                            <td class="border border-gray-300 p-2 text-right font-bold text-emerald-700">
                                - {{ number_format($stats['paidAmount'], 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @endif
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="4" class="border border-gray-300 p-2 text-right uppercase tracking-wider text-gray-900">Solde Dû à Livraison :</td>
                        <td class="border border-gray-300 p-2 text-right font-black text-sm text-gray-950">
                            {{ number_format($stats['remainingAmount'], 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes et Instructions de Livraison -->
        @if($order->notes)
            <div class="border border-gray-300 rounded-lg p-3 mb-6 bg-gray-50/30">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-600 mb-1">
                    Conditions & Instructions Particulières :
                </p>
                <p class="text-xs text-gray-800 whitespace-pre-line leading-relaxed">
                    {{ $order->notes }}
                </p>
            </div>
        @endif

        <!-- Cadres d'Émargement / Signatures -->
        <div class="grid grid-cols-2 gap-8 pt-4 mb-8">
            <div class="border border-gray-300 rounded-lg p-3 text-center h-28 flex flex-col justify-between">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-600 border-b border-gray-200 pb-1">
                    Pour l'Établissement (Visa & Signature)
                </p>
                <div class="text-[10px] text-gray-400 italic">Signature & Cachet</div>
            </div>

            <div class="border border-gray-300 rounded-lg p-3 text-center h-28 flex flex-col justify-between">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-600 border-b border-gray-200 pb-1">
                    Pour le Fournisseur (Bon pour accord & Date)
                </p>
                <div class="text-[10px] text-gray-400 italic">Date & Signature du livreur / commercial</div>
            </div>
        </div>

        <!-- Pied de page mentions -->
        <div class="border-t border-gray-200 pt-3 flex items-center justify-between text-[10px] text-gray-400">
            <span>{{ $shopName }} • Logiciel de Gestion Professionnel</span>
            <span>Document émis le {{ now()->format('d/m/Y à H:i') }}</span>
        </div>
    </div>

    <!-- ─── 6. Styles Print CSS A4 ─────────────────────────────────────────── -->
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }

            /* 1. Hide default page elements */
            body * {
                visibility: hidden;
            }

            /* 2. Hide Filament shell components */
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .fi-header-actions,
            .fi-modal,
            button {
                display: none !important;
            }

            /* 3. Keep layout containers visible so children can be visible */
            html,
            body,
            .fi-layout,
            .fi-main,
            .fi-page,
            .fi-section {
                visibility: visible !important;
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            /* 4. Display the printable purchase order */
            #purchase-order-printable,
            #purchase-order-printable * {
                visibility: visible !important;
            }

            #purchase-order-printable {
                display: block !important;
                position: relative !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
        }
    </style>
</x-filament-panels::page>
