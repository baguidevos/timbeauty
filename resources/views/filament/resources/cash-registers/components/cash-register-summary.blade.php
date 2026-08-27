@php
    $record = $getRecord();

    // 1. Ventes et encaissements
    $openedAt = $record->openedAt ?? $record->created_at;
    $closedAt = $record->closedAt ?? now()->addDay();

    $sales = \App\Models\Sale::where(function ($query) use ($record, $openedAt, $closedAt) {
            $query->where('cashRegisterId', $record->id)
                ->orWhere(function ($q) use ($openedAt, $closedAt) {
                    $q->whereNull('cashRegisterId')
                      ->whereBetween('created_at', [$openedAt, $closedAt]);
                });
        })
        ->with(['items', 'client', 'barber', 'creator'])
        ->get();
    
    // Encaissements espèces (ventes en cash)
    $cashSalesAmount = $sales->where('paymentMethod', 'cash')->sum('total');
    
    // Dépenses
    $expenses = \App\Models\Expense::where(function ($query) use ($record, $openedAt, $closedAt) {
            $query->where('cashRegisterId', $record->id)
                ->orWhere(function ($q) use ($openedAt, $closedAt) {
                    $q->whereNull('cashRegisterId')
                      ->whereBetween('created_at', [$openedAt, $closedAt]);
                });
        })
        ->with('category')
        ->get();

    // Dépenses espèces
    $cashExpensesAmount = $expenses->where('paymentMethod', 'cash')->sum('amount');

    // Apports & Injections de trésorerie (Avances propriétaire, dépôts de caisse)
    $extraInflows = (float) $record->transactions()
        ->whereIn('type', ['deposit', 'owner_contribution', 'in'])
        ->sum('amount');

    // Sorties hors charges (Remises en banque / écrémages, remboursements propriétaire, retraits)
    $extraOutflows = (float) $record->transactions()
        ->whereIn('type', ['withdrawal', 'owner_refund', 'bank_deposit', 'out'])
        ->sum('amount');

    // Solde d'ouverture
    $openingAmount = (float) ($record->openingAmount ?? 0);

    // Solde théorique officiel
    $theoreticalBalance = $record->getTheoreticalBalance();


    // 2. Bilan par type (Prestations vs Produits)
    $allSaleItems = $sales->flatMap->items;
    
    $servicesItems = $allSaleItems->where('type', 'service');
    $servicesCount = $servicesItems->sum('quantity');
    $servicesTotal = $servicesItems->sum('total');

    $productsItems = $allSaleItems->where('type', 'product');
    $productsCount = $productsItems->sum('quantity');
    $productsTotal = $productsItems->sum('total');

    $otherItems = $allSaleItems->whereNotIn('type', ['service', 'product']);
    $otherCount = $otherItems->sum('quantity');
    $otherTotal = $otherItems->sum('total');

    // 3. Bilan par catégorie
    $categoriesSummary = [];
    
    // Catégories de services
    foreach ($servicesItems as $item) {
        $service = \App\Models\Service::with('category')->find($item->itemId);
        $catName = $service?->category?->name ?? 'Prestations diverses';
        if (!isset($categoriesSummary[$catName])) {
            $categoriesSummary[$catName] = ['count' => 0, 'total' => 0, 'type' => 'Prestation'];
        }
        $categoriesSummary[$catName]['count'] += (int) $item->quantity;
        $categoriesSummary[$catName]['total'] += (float) $item->total;
    }

    // Catégories de produits
    foreach ($productsItems as $item) {
        $product = \App\Models\Product::with('category')->find($item->itemId);
        $catName = $product?->category?->name ?? 'Produits divers';
        if (!isset($categoriesSummary[$catName])) {
            $categoriesSummary[$catName] = ['count' => 0, 'total' => 0, 'type' => 'Produit'];
        }
        $categoriesSummary[$catName]['count'] += (int) $item->quantity;
        $categoriesSummary[$catName]['total'] += (float) $item->total;
    }

    // 4. Répartition par Mode de Paiement
    $paymentMethods = [
        'cash' => ['label' => 'Espèces', 'color' => 'emerald', 'total' => 0],
        'card' => ['label' => 'Carte bancaire', 'color' => 'sky', 'total' => 0],
        'transfer' => ['label' => 'Virement', 'color' => 'indigo', 'total' => 0],
        'check' => ['label' => 'Chèque', 'color' => 'amber', 'total' => 0],
        'other' => ['label' => 'Autre', 'color' => 'slate', 'total' => 0],
    ];

    foreach ($sales as $sale) {
        $method = $sale->paymentMethod ?? 'cash';
        if (isset($paymentMethods[$method])) {
            $paymentMethods[$method]['total'] += (float) $sale->total;
        } else {
            $paymentMethods['other']['total'] += (float) $sale->total;
        }
    }
@endphp

<div class="space-y-6">
    {{-- Bandeau d'état supérieur --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50/70 p-4.5 dark:border-amber-900/50 dark:bg-amber-950/30">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                Caisse du {{ $record->openedAt ? $record->openedAt->format('d/m/Y') : now()->format('d/m/Y') }}
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-400">
                Ouverte le {{ $record->openedAt ? $record->openedAt->format('d/m/Y à H:i') : '-' }} par {{ $record->opener?->name ?? 'Administrateur' }}
            </p>
        </div>
        <div>
            @if ($record->isOpen())
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                    EN COURS (OUVERTE)
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <span class="h-2 w-2 rounded-full bg-gray-500"></span>
                    CLÔTURÉE le {{ $record->closedAt ? $record->closedAt->format('d/m/Y à H:i') : '' }}
                </span>
            @endif
        </div>
    </div>

    {{-- 4 Cartes KPI Financières --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Solde d'ouverture --}}
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-[11px] font-bold tracking-wider text-gray-400 uppercase dark:text-gray-500">
                Solde d'ouverture (Report)
            </span>
            <div class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">
                {{ \App\Helpers\FormatHelper::formatFCFA($openingAmount) }}
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                Provenance clôture précédente
            </p>
        </div>

        {{-- Encaissé espèces --}}
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase dark:text-emerald-400">
                Encaissé espèces (+)
            </span>
            <div class="mt-2 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">
                +{{ \App\Helpers\FormatHelper::formatFCFA($cashSalesAmount) }}
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                Ventes & prestations réglées en cash
            </p>
        </div>

        {{-- Dépenses espèces --}}
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-[11px] font-bold tracking-wider text-rose-600 uppercase dark:text-rose-400">
                Dépenses espèces (-)
            </span>
            <div class="mt-2 text-2xl font-extrabold text-rose-600 dark:text-rose-400">
                -{{ \App\Helpers\FormatHelper::formatFCFA($cashExpensesAmount) }}
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                Décaissements du jour
            </p>
        </div>

        {{-- Solde théorique --}}
        <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-[11px] font-bold tracking-wider text-sky-600 uppercase dark:text-sky-400">
                Solde théorique de caisse
            </span>
            <div class="mt-2 text-2xl font-extrabold text-sky-600 dark:text-sky-400">
                {{ \App\Helpers\FormatHelper::formatFCFA($theoreticalBalance) }}
            </div>
            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                @if ($extraInflows > 0 && $extraOutflows > 0)
                    Ouverture + Ventes + Apports - Dépenses - Remises
                @elseif ($extraInflows > 0)
                    Ouverture + Ventes + Apports - Dépenses
                @elseif ($extraOutflows > 0)
                    Ouverture + Ventes - Dépenses - Remises
                @else
                    Ouverture + Ventes - Dépenses
                @endif
            </p>
        </div>
    </div>

    @if ($extraInflows > 0 || $extraOutflows > 0)
        {{-- Mouvements de trésorerie complémentaires (Apports, Banques, etc.) --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @if ($extraInflows > 0)
                <div class="flex items-center justify-between rounded-xl border border-purple-200 bg-purple-50/70 p-3.5 text-xs dark:border-purple-900/50 dark:bg-purple-950/30">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300">
                            🤝
                        </span>
                        <div>
                            <span class="font-bold text-purple-900 dark:text-purple-200">Apports & Avances de trésorerie</span>
                            <p class="text-[11px] text-purple-700/80 dark:text-purple-300/80">Injections de fonds personnels</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-extrabold text-purple-700 dark:text-purple-300">+{{ \App\Helpers\FormatHelper::formatFCFA($extraInflows) }}</span>
                    </div>
                </div>
            @endif

            @if ($extraOutflows > 0)
                <div class="flex items-center justify-between rounded-xl border border-indigo-200 bg-indigo-50/70 p-3.5 text-xs dark:border-indigo-900/50 dark:bg-indigo-950/30">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            🏦
                        </span>
                        <div>
                            <span class="font-bold text-indigo-900 dark:text-indigo-200">Remises en Banque & Remboursements</span>
                            <p class="text-[11px] text-indigo-700/80 dark:text-indigo-300/80">Écrémages et remboursements</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-extrabold text-indigo-700 dark:text-indigo-300">-{{ \App\Helpers\FormatHelper::formatFCFA($extraOutflows) }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Bilan des Ventes par Type --}}
    <div class="overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/50 px-5 py-3.5 dark:border-gray-800 dark:bg-gray-800/30">
            <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            <h4 class="text-xs font-bold text-gray-800 uppercase dark:text-gray-200">
                Bilan des Ventes par Type d'Activité
            </h4>
        </div>

        <table class="w-full text-left text-xs">
            <thead class="border-b border-gray-100 bg-gray-50/30 text-[11px] font-semibold text-gray-500 uppercase dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3">Type de prestation / produit</th>
                    <th class="px-5 py-3 text-center">Quantité vendue</th>
                    <th class="px-5 py-3 text-right">Chiffre d'affaires</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                    <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                        ✂️ Prestations de coiffure & barbiers
                    </td>
                    <td class="px-5 py-3.5 text-center font-semibold text-gray-700 dark:text-gray-300">
                        {{ $servicesCount }}
                    </td>
                    <td class="px-5 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                        {{ \App\Helpers\FormatHelper::formatFCFA($servicesTotal) }}
                    </td>
                </tr>
                <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                    <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                        🧴 Produits cosmétiques & soins
                    </td>
                    <td class="px-5 py-3.5 text-center font-semibold text-gray-700 dark:text-gray-300">
                        {{ $productsCount }}
                    </td>
                    <td class="px-5 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                        {{ \App\Helpers\FormatHelper::formatFCFA($productsTotal) }}
                    </td>
                </tr>
                @if ($otherCount > 0)
                    <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                            📦 Autres articles / prestations
                        </td>
                        <td class="px-5 py-3.5 text-center font-semibold text-gray-700 dark:text-gray-300">
                            {{ $otherCount }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                            {{ \App\Helpers\FormatHelper::formatFCFA($otherTotal) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Bilan par Catégorie --}}
    <div class="overflow-hidden rounded-xl border border-gray-200/80 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50/50 px-5 py-3.5 dark:border-gray-800 dark:bg-gray-800/30">
            <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6Z" />
            </svg>
            <h4 class="text-xs font-bold text-gray-800 uppercase dark:text-gray-200">
                Bilan par Catégorie
            </h4>
        </div>

        @if (count($categoriesSummary) > 0)
            <table class="w-full text-left text-xs">
                <thead class="border-b border-gray-100 bg-gray-50/30 text-[11px] font-semibold text-gray-500 uppercase dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Catégorie</th>
                        <th class="px-5 py-3 text-center">Articles / Actes vendus</th>
                        <th class="px-5 py-3 text-right">Total (FCFA)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($categoriesSummary as $categoryName => $catData)
                        <tr class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                            <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    {{ $catData['type'] }}
                                </span>
                                <span class="ml-2 font-semibold">{{ $categoryName }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center font-semibold text-gray-700 dark:text-gray-300">
                                {{ $catData['count'] }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                {{ \App\Helpers\FormatHelper::formatFCFA($catData['total']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-5 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                Aucune vente enregistrée pour cette journée.
            </div>
        @endif
    </div>

    {{-- Répartition par Mode de Paiement --}}
    <div class="rounded-xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-2 pb-4">
            <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 2.25 19.5Z" />
            </svg>
            <h4 class="text-xs font-bold text-gray-800 uppercase dark:text-gray-200">
                Répartition par Mode de Paiement
            </h4>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($paymentMethods as $key => $method)
                <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 transition hover:border-gray-200 dark:border-gray-800 dark:bg-gray-800/40">
                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                        {{ $method['label'] }}
                    </span>
                    <div class="mt-1 text-base font-extrabold text-gray-900 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($method['total']) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
