@php
    $record = $record ?? null;
    if (!$record && isset($this) && method_exists($this, 'getRecord')) {
        $record = $this->getRecord();
    }

    $historyItems = collect();

    if ($record) {
        $openedAt = $record->openedAt ?? $record->created_at;
        $closedAt = $record->closedAt ?? now()->addDay();

        // 1. Ventes
        $sales = \App\Models\Sale::where(function ($query) use ($record, $openedAt, $closedAt) {
                $query->where('cashRegisterId', $record->id)
                    ->orWhere(function ($q) use ($openedAt, $closedAt) {
                        $q->whereNull('cashRegisterId')
                          ->whereBetween('created_at', [$openedAt, $closedAt]);
                    });
            })
            ->with(['client', 'barber', 'items', 'creator'])
            ->get();

        foreach ($sales as $sale) {
            $itemsSummary = $sale->items->pluck('name')->join(', ');
            $historyItems->push([
                'timestamp' => $sale->created_at,
                'time' => $sale->created_at->format('H:i'),
                'type' => 'sale',
                'title' => 'Encaissement Vente / Prestation',
                'badge' => $sale->paymentMethod === 'cash' ? 'Espèces' : ($sale->paymentMethod ?? 'Autre'),
                'badgeColor' => $sale->paymentMethod === 'cash' ? 'emerald' : 'sky',
                'description' => ($sale->client ? 'Client : ' . $sale->client->firstName . ' ' . $sale->client->lastName . ' — ' : '') . ($itemsSummary ?: 'Prestation/Article'),
                'author' => $sale->creator?->name ?? ($sale->barber?->firstName ?? 'Staff'),
                'amount' => (float) $sale->total,
                'isPositive' => true,
            ]);
        }

        // 2. Dépenses
        $expenses = \App\Models\Expense::where(function ($query) use ($record, $openedAt, $closedAt) {
                $query->where('cashRegisterId', $record->id)
                    ->orWhere(function ($q) use ($openedAt, $closedAt) {
                        $q->whereNull('cashRegisterId')
                          ->whereBetween('created_at', [$openedAt, $closedAt]);
                    });
            })
            ->with(['category', 'creator'])
            ->get();

        foreach ($expenses as $expense) {
            $historyItems->push([
                'timestamp' => $expense->created_at,
                'time' => $expense->created_at->format('H:i'),
                'type' => 'expense',
                'title' => 'Décaissement Dépense (' . ($expense->category?->name ?? 'Générale') . ')',
                'badge' => $expense->paymentMethod === 'cash' ? 'Espèces' : ($expense->paymentMethod ?? 'Autre'),
                'badgeColor' => 'rose',
                'description' => ($expense->beneficiary ? 'Bénéficiaire : ' . $expense->beneficiary . ' — ' : '') . ($expense->description ?: 'Charge d\'exploitation'),
                'author' => $expense->creator?->name ?? 'Staff',
                'amount' => (float) $expense->amount,
                'isPositive' => false,
            ]);
        }


        // 3. Transactions de caisse manuelles (Dépôts / Retraits / Ajustements)
        foreach ($record->transactions()->whereNotIn('type', ['sale', 'expense'])->with('creator')->get() as $trx) {
            $isPos = in_array($trx->type, ['deposit', 'adjustment']);
            $historyItems->push([
                'timestamp' => $trx->created_at,
                'time' => $trx->created_at->format('H:i'),
                'type' => $trx->type,
                'title' => match ($trx->type) {
                    'deposit' => 'Apport / Dépôt de caisse',
                    'withdrawal' => 'Prélèvement / Retrait de caisse',
                    'adjustment' => 'Ajustement de caisse',
                    default => 'Mouvement de caisse',
                },
                'badge' => 'Caisse',
                'badgeColor' => $isPos ? 'emerald' : 'amber',
                'description' => $trx->description ?: 'Mouvement de fonds',
                'author' => $trx->creator?->name ?? 'Admin',
                'amount' => (float) $trx->amount,
                'isPositive' => $isPos,
            ]);
        }
    }

    $sortedHistory = $historyItems->sortByDesc('timestamp');

    $totalIn = $historyItems->where('isPositive', true)->sum('amount');
    $totalOut = $historyItems->where('isPositive', false)->sum('amount');
    $netBalance = $totalIn - $totalOut;
@endphp

<div class="space-y-6">
    {{-- Liste chronologique des opérations --}}
    <div class="space-y-3">
        <h4 class="text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">
            Journal chronologique des opérations ({{ $sortedHistory->count() }})
        </h4>

        @if ($sortedHistory->isNotEmpty())
            <div class="divide-y divide-gray-100 rounded-xl border border-gray-200/80 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                @foreach ($sortedHistory as $item)
                    <div class="flex items-start justify-between gap-3 p-3.5 transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                        <div class="flex items-start gap-3">
                            <span class="rounded-lg bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $item['time'] }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $item['title'] }}
                                    </h5>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ $item['badge'] }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $item['description'] }}
                                </p>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                    Par {{ $item['author'] }}
                                </span>
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <span class="text-xs font-bold {{ $item['isPositive'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $item['isPositive'] ? '+' : '-' }}{{ \App\Helpers\FormatHelper::formatFCFA($item['amount']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                Aucune transaction enregistrée pour cette session.
            </div>
        @endif
    </div>

    {{-- Sommaire récapitulatif final --}}
    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4.5 dark:border-amber-900/50 dark:bg-amber-950/30">
        <h4 class="text-xs font-bold tracking-wider text-amber-800 uppercase dark:text-amber-400">
            Sommaire de la journée
        </h4>
        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
            <div class="rounded-lg bg-white/80 p-2.5 shadow-sm dark:bg-gray-900/80">
                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Total Entrées</span>
                <div class="mt-1 font-extrabold text-emerald-600 dark:text-emerald-400">
                    +{{ \App\Helpers\FormatHelper::formatFCFA($totalIn) }}
                </div>
            </div>
            <div class="rounded-lg bg-white/80 p-2.5 shadow-sm dark:bg-gray-900/80">
                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Total Sorties</span>
                <div class="mt-1 font-extrabold text-rose-600 dark:text-rose-400">
                    -{{ \App\Helpers\FormatHelper::formatFCFA($totalOut) }}
                </div>
            </div>
            <div class="rounded-lg bg-white/80 p-2.5 shadow-sm dark:bg-gray-900/80">
                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Flux Net</span>
                <div class="mt-1 font-extrabold text-sky-600 dark:text-sky-400">
                    {{ \App\Helpers\FormatHelper::formatFCFA($netBalance) }}
                </div>
            </div>
        </div>
    </div>
</div>
