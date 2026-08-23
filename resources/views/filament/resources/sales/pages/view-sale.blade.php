<x-filament-panels::page>
    @php
        $sale = $record;
        $stats = $this->getSaleStats();
        $items = $this->getItemsList();
        $promotion = $this->getAppliedPromotion();

        $statusColor = match($sale->status) {
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };

        $statusLabel = match($sale->status) {
            'completed' => 'Vente Terminée & Encaissée',
            'pending' => 'En attente de règlement',
            'cancelled' => 'Vente Annulée',
            default => $sale->status,
        };

        $paymentIcon = match($sale->paymentMethod) {
            'cash' => 'heroicon-m-banknotes',
            'tmoney', 'flooz' => 'heroicon-m-device-phone-mobile',
            'card' => 'heroicon-m-credit-card',
            'transfer' => 'heroicon-m-arrow-path',
            default => 'heroicon-m-wallet',
        };

        $paymentLabel = match($sale->paymentMethod) {
            'cash' => 'Espèces (Cash)',
            'tmoney' => 'TMoney Mobile',
            'flooz' => 'Moov Flooz',
            'card' => 'Carte Bancaire',
            'transfer' => 'Virement Bancaire',
            'other' => 'Autre moyen',
            default => $sale->paymentMethod,
        };
    @endphp

    <div class="space-y-6">
        <!-- ─── 1. Bandeau En-tête de la Vente ──────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Identité Vente -->
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/20">
                        <x-heroicon-o-receipt-percent class="h-7 w-7" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 class="text-xl font-black tracking-tight text-gray-950 dark:text-white">
                                Reçu de Vente #{{ $sale->id }}
                            </h2>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1.5 font-medium">
                                <x-heroicon-m-calendar class="h-3.5 w-3.5 text-gray-400" />
                                {{ $sale->created_at ? $sale->created_at->format('d/m/Y à H:i') : 'Date non définie' }}
                            </span>
                            <span class="flex items-center gap-1.5 font-semibold text-gray-700 dark:text-gray-300">
                                <x-heroicon-m-wallet class="h-3.5 w-3.5 text-amber-500" />
                                {{ $paymentLabel }}
                            </span>
                            @if($sale->cashRegister)
                                <span class="flex items-center gap-1.5 text-gray-500">
                                    <x-heroicon-m-building-storefront class="h-3.5 w-3.5 text-gray-400" />
                                    Session Caisse #{{ $sale->cashRegister->id }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Montant & Impression Rapide -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 border-t border-gray-100 pt-4 lg:border-t-0 lg:pt-0">
                    <div class="text-left sm:text-right">
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Montant Réglé</p>
                        <p class="text-2xl font-black text-amber-600 dark:text-amber-400">
                            {{ \App\Helpers\FormatHelper::formatFCFA($stats['total']) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 2. Cartes KPIs Financiers & Volumes ────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <!-- Total Net Encaissé -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Net Payé</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <x-heroicon-o-banknotes class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['total']) }}
                    </p>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">
                        Règlement complet
                    </p>
                </div>
            </div>

            <!-- Sous-Total Brut -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Sous-Total Brut</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400">
                        <x-heroicon-o-calculator class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['subtotal']) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        Tarif catalogue brut
                    </p>
                </div>
            </div>

            <!-- Remise & Économies -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Remise / Promo</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <x-heroicon-o-tag class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black {{ $stats['discountAmount'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-950 dark:text-white' }}">
                        {{ $stats['discountAmount'] > 0 ? '- ' . \App\Helpers\FormatHelper::formatFCFA($stats['discountAmount']) : '0 FCFA' }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        {{ $stats['discountPercentage'] > 0 ? "({$stats['discountPercentage']}% d'économie)" : "Aucune remise" }}
                    </p>
                </div>
            </div>

            <!-- Articles & Points Fidélité -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Volume & Fidélité</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                        <x-heroicon-o-sparkles class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xl font-black text-gray-950 dark:text-white">
                        {{ $stats['totalItems'] }} article(s)
                    </p>
                    <p class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold mt-0.5">
                        {{ $stats['servicesCount'] }} prestation(s) • {{ $stats['productsCount'] }} produit(s)
                    </p>
                </div>
            </div>
        </div>

        <!-- ─── 3. Cartes Intervenants (Client, Coiffeur, Caisse & RDV) ─────── -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Client -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-m-user class="h-3.5 w-3.5 text-amber-500" />
                        Client
                    </span>
                    @if($sale->client)
                        <a href="{{ \App\Filament\Resources\Clients\ClientResource::getUrl('view', ['record' => $sale->client]) }}" class="text-[11px] font-bold text-amber-600 hover:underline dark:text-amber-400">
                            Fiche Client →
                        </a>
                    @endif
                </div>

                @if($sale->client)
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                            {{ mb_substr($sale->client->firstName, 0, 1) }}{{ mb_substr($sale->client->lastName, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                                {{ $sale->client->firstName }} {{ $sale->client->lastName }}
                            </p>
                            <p class="truncate text-xs text-gray-400 font-mono">
                                {{ $sale->client->phone ?: 'Sans téléphone' }}
                            </p>
                        </div>
                    </div>
                    @if($sale->client->loyaltyTier)
                        <div class="mt-3 flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2 text-xs">
                            <span class="text-gray-500 font-medium">Programme VIP</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">
                                {{ $sale->client->loyaltyTier->name }} ({{ $sale->client->loyaltyPoints }} pts)
                            </span>
                        </div>
                    @endif
                @else
                    <div class="py-2 text-center text-gray-400 text-xs">
                        <p class="font-semibold text-gray-600 dark:text-gray-300">Client de passage</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Vente anonyme au comptoir</p>
                    </div>
                @endif
            </div>

            <!-- Coiffeur / Prestataire -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-m-scissors class="h-3.5 w-3.5 text-amber-500" />
                        Coiffeur Référent
                    </span>
                    @if($sale->barber)
                        <a href="{{ \App\Filament\Resources\Barbers\BarberResource::getUrl('view', ['record' => $sale->barber]) }}" class="text-[11px] font-bold text-amber-600 hover:underline dark:text-amber-400">
                            Fiche Coiffeur →
                        </a>
                    @endif
                </div>

                @if($sale->barber)
                    <div class="flex items-center gap-3">
                        @if($sale->barber->photo)
                            <img src="{{ Storage::url($sale->barber->photo) }}" class="h-10 w-10 rounded-xl object-cover border border-gray-200 dark:border-gray-700" alt="{{ $sale->barber->firstName }}">
                        @else
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ mb_substr($sale->barber->firstName, 0, 1) }}{{ mb_substr($sale->barber->lastName, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                                {{ $sale->barber->firstName }} {{ $sale->barber->lastName }}
                            </p>
                            <p class="truncate text-xs text-gray-400 font-mono">
                                {{ $sale->barber->phone ?: 'Coiffeur de l\'équipe' }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="py-2 text-center text-gray-400 text-xs">
                        <p class="font-semibold text-gray-600 dark:text-gray-300">Non spécifié</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Vente globale au salon</p>
                    </div>
                @endif
            </div>

            <!-- Caisse & Opérateur -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-m-user-circle class="h-3.5 w-3.5 text-amber-500" />
                        Encaissé Par
                    </span>
                </div>

                <div class="space-y-1.5 text-xs">
                    <p class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ $sale->creator->name ?? 'Opérateur Caisse' }}
                    </p>
                    <p class="text-gray-400">
                        {{ $sale->creator->email ?? 'admin@barbershop.com' }}
                    </p>
                    @if($sale->cashRegister)
                        <p class="text-[11px] text-gray-500 pt-1 border-t border-gray-100 dark:border-gray-800">
                            Caisse : <span class="font-semibold text-gray-700 dark:text-gray-300">Session #{{ $sale->cashRegister->id }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Rendez-vous associé -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <x-heroicon-m-calendar-days class="h-3.5 w-3.5 text-amber-500" />
                        Rendez-vous Lié
                    </span>
                </div>

                @if($sale->appointment)
                    <div class="space-y-1 text-xs">
                        <p class="font-bold text-gray-900 dark:text-white">
                            RDV du {{ \Carbon\Carbon::parse($sale->appointment->date)->format('d/m/Y') }}
                        </p>
                        <p class="text-gray-500 dark:text-gray-400">
                            Créneau : {{ $sale->appointment->startTime }} - {{ $sale->appointment->endTime }}
                        </p>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 mt-1">
                            Prestation terminée
                        </span>
                    </div>
                @else
                    <div class="py-2 text-center text-gray-400 text-xs">
                        <p class="font-semibold text-gray-600 dark:text-gray-300">Vente directe (Sans RDV)</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Encaissement direct en caisse</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- ─── 4. Tableau Détaillé des Articles & Lignes de Vente ───────────── -->
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="h-4 w-4 text-amber-500" />
                    Articles & Prestations Encaissés ({{ $items->count() }})
                </h3>
                <span class="text-xs text-gray-400 font-medium">Détail des lignes de ticket</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="p-3.5">Type</th>
                            <th class="p-3.5">Désignation</th>
                            <th class="p-3.5 text-right">Prix Unitaire</th>
                            <th class="p-3.5 text-center">Quantité</th>
                            <th class="p-3.5 text-right">Remise Ligne</th>
                            <th class="p-3.5 text-right">Total Ligne</th>
                            <th class="p-3.5 text-center">Statut / Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @foreach($items as $item)
                            @php
                                $service = $item->type === 'service' ? $this->getServiceForItem($item) : null;
                                $isPromoted = $this->isItemEligibleForSalePromotion($item, $promotion);
                                $isCancelled = $item->status === 'cancelled';
                                $isReturned = $item->status === 'returned';
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition {{ ($isCancelled || $isReturned) ? 'opacity-60 bg-gray-50/40 dark:bg-gray-900/40' : '' }}">
                                <td class="p-3.5">
                                    @if($item->type === 'service')
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800">
                                            <x-heroicon-m-scissors class="h-3 w-3 text-amber-500" />
                                            Prestation
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800">
                                            <x-heroicon-m-cube class="h-3 w-3 text-emerald-500" />
                                            Produit
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-bold {{ ($isCancelled || $isReturned) ? 'line-through text-gray-500' : 'text-gray-900 dark:text-white' }}">
                                            {{ $item->name }}
                                        </p>
                                        @if($service && $service->category)
                                            <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                                {{ $service->category->name }}
                                            </span>
                                        @endif
                                        @if($isPromoted && $promotion)
                                            <span 
                                                title="Catégorie éligible à la promotion {{ $promotion->name }}" 
                                                class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-white px-2 py-0.5 text-[10px] font-black shadow-xs"
                                            >
                                                <x-heroicon-m-tag class="h-2.5 w-2.5 stroke-[2.5]" />
                                                Promo {{ $promotion->formatted_value }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($item->cancelReason)
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 italic mt-0.5">
                                            Motif : {{ $item->cancelReason }}
                                        </p>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right text-gray-600 dark:text-gray-300 font-semibold">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($item->unitPrice) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="inline-flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 px-2 py-0.5 font-bold text-gray-800 dark:text-gray-200 text-xs">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-right">
                                    @if($item->discount > 0)
                                        <span class="text-rose-600 font-bold">-{{ \App\Helpers\FormatHelper::formatFCFA($item->discount) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right font-black {{ ($isCancelled || $isReturned) ? 'line-through text-gray-400' : 'text-gray-950 dark:text-white' }}">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($item->total) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    @if($isReturned)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-300">
                                            📦 Retourné (x{{ $item->refundedQuantity }})
                                        </span>
                                    @elseif($isCancelled)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-300 dark:bg-zinc-800 dark:text-zinc-300">
                                            ❌ Annulée
                                        </span>
                                    @elseif($sale->status !== 'cancelled')
                                        @if($item->type === 'product')
                                            <button
                                                wire:click="openReturnModal({{ $item->id }})"
                                                type="button"
                                                class="inline-flex items-center gap-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 px-2.5 py-1 text-[11px] font-bold border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800 transition shadow-2xs"
                                                title="Enregistrer un retour produit et réintégrer le stock"
                                            >
                                                <x-heroicon-m-arrow-uturn-left class="h-3 w-3" />
                                                Retourner
                                            </button>
                                        @else
                                            <button
                                                wire:click="openCancelServiceModal({{ $item->id }})"
                                                type="button"
                                                class="inline-flex items-center gap-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 px-2.5 py-1 text-[11px] font-bold border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 transition shadow-2xs"
                                                title="Annuler cette prestation et ajuster la facture"
                                            >
                                                <x-heroicon-m-x-mark class="h-3 w-3" />
                                                Annuler
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/80 dark:bg-gray-800/80 border-t-2 border-gray-200 dark:border-gray-700 font-bold text-xs">
                        <tr>
                            <td colspan="5" class="p-3.5 text-right uppercase tracking-wider text-gray-500">Sous-total Brut :</td>
                            <td colspan="2" class="p-3.5 text-right text-gray-900 dark:text-white">
                                {{ \App\Helpers\FormatHelper::formatFCFA($stats['subtotal']) }}
                            </td>
                        </tr>
                        @if($stats['discountAmount'] > 0)
                            <tr class="text-rose-600 dark:text-rose-400">
                                <td colspan="5" class="p-2.5 text-right uppercase tracking-wider">Remise Déduite :</td>
                                <td colspan="2" class="p-2.5 text-right">
                                    - {{ \App\Helpers\FormatHelper::formatFCFA($stats['discountAmount']) }}
                                </td>
                            </tr>
                        @endif
                        <tr class="text-sm font-black bg-amber-500/10 text-amber-900 dark:text-amber-300">
                            <td colspan="5" class="p-3.5 text-right uppercase tracking-wider">TOTAL NET FACTURE :</td>
                            <td colspan="2" class="p-3.5 text-right text-base text-amber-600 dark:text-amber-400">
                                {{ \App\Helpers\FormatHelper::formatFCFA($stats['total']) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ─── 5. Promotion Appliquée & Notes Internes ────────────────────── -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <!-- Promotion / Remise Appliquée -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-tag class="h-4 w-4 text-amber-500" />
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                        Offre Promotionnelle & Avantages
                    </h3>
                </div>

                @if($promotion)
                    <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/20 text-amber-800 dark:text-amber-300 px-1.5 py-0.5 text-[10px] font-extrabold uppercase">
                                        <x-heroicon-m-sparkles class="h-3 w-3 text-amber-500" />
                                        Offre Spéciale
                                    </span>
                                    <p class="font-bold text-amber-900 dark:text-amber-200 text-sm">
                                        {{ $promotion->name }}
                                    </p>
                                </div>
                                <p class="text-xs text-amber-700/80 dark:text-amber-400/80 mt-1.5">
                                    {{ $promotion->description ?: 'Promotion marketing appliquée lors de l\'encaissement' }}
                                </p>
                            </div>
                            <span class="rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-white px-2.5 py-1 text-xs font-black shadow-xs shrink-0">
                                {{ $promotion->formatted_value }}
                            </span>
                        </div>
                    </div>
                @elseif($stats['discountAmount'] > 0)
                    <div class="rounded-xl border border-sky-200 bg-sky-50/50 p-4 dark:border-sky-900/50 dark:bg-sky-950/20">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-sky-500/20 text-sky-800 dark:text-sky-300 px-1.5 py-0.5 text-[10px] font-extrabold uppercase">
                                        <x-heroicon-m-scissors class="h-3 w-3 text-sky-500" />
                                        Remise Directe
                                    </span>
                                    <p class="font-bold text-sky-900 dark:text-sky-200 text-sm">
                                        Remise Manuelle en Caisse
                                    </p>
                                </div>
                                <p class="text-xs text-sky-700/80 dark:text-sky-400/80 mt-1.5">
                                    Geste commercial accordé directement lors de la finalisation du panier.
                                </p>
                            </div>
                            <span class="rounded-full bg-sky-600 text-white px-2.5 py-1 text-xs font-black shadow-xs shrink-0">
                                - {{ \App\Helpers\FormatHelper::formatFCFA($stats['discountAmount']) }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="p-3 rounded-xl border border-dashed border-gray-200 dark:border-gray-800 text-center">
                        <p class="text-xs text-gray-400 italic">
                            Aucune remise ni offre promotionnelle appliquée (Facturation plein tarif).
                        </p>
                    </div>
                @endif
            </div>

            <!-- Notes Internes -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-o-chat-bubble-left-ellipsis class="h-4 w-4 text-gray-400" />
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                        Notes & Commentaires
                    </h3>
                </div>

                @if($sale->notes)
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                        {{ $sale->notes }}
                    </p>
                @else
                    <p class="text-xs text-gray-400 italic">
                        Aucune note interne associée à ce ticket.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- ─── 6. Modales d'Action par Ligne (Retour Produit & Annulation Prestation) ─── -->
    <x-filament::modal id="return-product-modal" width="md" icon="heroicon-o-arrow-uturn-left" icon-color="danger">
        <x-slot name="heading">
            Retourner un produit en stock
        </x-slot>

        <x-slot name="description">
            Enregistrez le retour de cet article. La quantité choisie sera immédiatement réintégrée dans le stock du salon.
        </x-slot>

        <form wire:submit="submitReturnProduct" id="returnProductForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                    Quantité à retourner <span class="text-rose-500">*</span>
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="number"
                        wire:model="returnQuantity"
                        min="1"
                        required
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                    Motif du retour
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model="returnReason"
                        placeholder="Ex: Produit défectueux, changement d'avis client..."
                    />
                </x-filament::input.wrapper>
            </div>
        </form>

        <x-slot name="footerActions">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'return-product-modal' })"
                type="button"
                color="gray"
            >
                Fermer
            </x-filament::button>

            <x-filament::button
                wire:click="submitReturnProduct"
                type="submit"
                form="returnProductForm"
                color="danger"
                icon="heroicon-m-check"
            >
                Confirmer le Retour & Réintégrer Stock
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="cancel-service-modal" width="md" icon="heroicon-o-x-mark" icon-color="warning">
        <x-slot name="heading">
            Annuler une prestation
        </x-slot>

        <x-slot name="description">
            Annulez cette prestation pour ajuster la facture. Le montant sera automatiquement déduit du total réglé.
        </x-slot>

        <form wire:submit="submitCancelService" id="cancelServiceForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                    Motif de l'annulation <span class="text-rose-500">*</span>
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model="cancelServiceReason"
                        placeholder="Ex: Insatisfaction, geste commercial, erreur de caisse..."
                        required
                    />
                </x-filament::input.wrapper>
            </div>
        </form>

        <x-slot name="footerActions">
            <x-filament::button
                x-on:click="$dispatch('close-modal', { id: 'cancel-service-modal' })"
                type="button"
                color="gray"
            >
                Fermer
            </x-filament::button>

            <x-filament::button
                wire:click="submitCancelService"
                type="submit"
                form="cancelServiceForm"
                color="warning"
                icon="heroicon-m-check"
            >
                Confirmer l'Annulation
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <!-- ─── 7. Format Ticket Thermique / Style Print ────────────────────── -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .fi-page, .fi-page * {
                visibility: visible;
            }
            .fi-page {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .fi-header-actions, .fi-breadcrumbs, button {
                display: none !important;
            }
        }
    </style>
</x-filament-panels::page>
