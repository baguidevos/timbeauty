<div class="p-4 bg-white dark:bg-gray-900 rounded-xl text-gray-900 dark:text-gray-100 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-start border-b border-gray-200 dark:border-gray-800 pb-4 mb-4">
        <div>
            <h2 class="text-xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">BARBER SHOP PRESTIGE</h2>
            <p class="text-xs text-gray-500">Salon & Soins Esthétiques Hommes</p>
            <p class="text-xs text-gray-500">Kara, Togo • Tél : +228 90 00 00 00</p>
        </div>
        <div class="text-right">
            <span class="inline-block px-2.5 py-1 text-xs font-bold uppercase rounded bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-300">
                Bulletin de Paie
            </span>
            <p class="text-sm font-semibold mt-1">Période : {{ sprintf('%02d/%d', $record->month, $record->year) }}</p>
            <p class="text-xs text-gray-500">Édité le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Employee Details -->
    <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700 text-sm mb-4">
        <div>
            <span class="text-xs text-gray-500 uppercase block font-semibold">Employé(e)</span>
            <p class="font-bold text-base">{{ $record->barber?->firstName }} {{ $record->barber?->lastName }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">Tél : {{ $record->barber?->phone ?? 'N/A' }}</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-gray-500 uppercase block font-semibold">Poste / Rémunération</span>
            <p class="font-semibold capitalize">{{ $record->barber?->jobTitle ?? 'Employé' }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400">Mode : {{ match($record->barber?->remunerationType) {
                'fixed' => 'Salaire Fixe',
                'commission' => 'Commissions',
                'fixed_plus_commission' => 'Fixe + Commissions',
                'per_service' => 'Par Prestation',
                default => 'Standard'
            } }}</p>
        </div>
    </div>

    <!-- Items Breakdown Table -->
    <table class="w-full text-sm border-collapse mb-4">
        <thead>
            <tr class="border-b-2 border-gray-300 dark:border-gray-700 text-xs uppercase text-gray-500 font-semibold text-left">
                <th class="py-2">Rubrique</th>
                <th class="py-2 text-right">Gains (FCFA)</th>
                <th class="py-2 text-right">Retenues (FCFA)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            <tr>
                <td class="py-2 font-medium">Salaire de base / Fixe</td>
                <td class="py-2 text-right text-gray-900 dark:text-gray-100">{{ number_format($record->fixedSalary, 0, ',', ' ') }}</td>
                <td class="py-2 text-right text-gray-400">-</td>
            </tr>
            @if($record->commissions > 0)
            <tr>
                <td class="py-2 font-medium text-emerald-600 dark:text-emerald-400">Commissions sur prestations</td>
                <td class="py-2 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ number_format($record->commissions, 0, ',', ' ') }}</td>
                <td class="py-2 text-right text-gray-400">-</td>
            </tr>
            @endif
            @if($record->bonus > 0)
            <tr>
                <td class="py-2 font-medium text-blue-600 dark:text-blue-400">Primes & Gratifications</td>
                <td class="py-2 text-right font-medium text-blue-600 dark:text-blue-400">{{ number_format($record->bonus, 0, ',', ' ') }}</td>
                <td class="py-2 text-right text-gray-400">-</td>
            </tr>
            @endif
            @if($record->advances > 0)
            <tr>
                <td class="py-2 font-medium text-orange-600 dark:text-orange-400">Avances & Acomptes sur salaire</td>
                <td class="py-2 text-right text-gray-400">-</td>
                <td class="py-2 text-right font-medium text-orange-600 dark:text-orange-400">{{ number_format($record->advances, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @if($record->deductions > 0)
            <tr>
                <td class="py-2 font-medium text-rose-600 dark:text-rose-400">Retenues & Déductions diverses</td>
                <td class="py-2 text-right text-gray-400">-</td>
                <td class="py-2 text-right font-medium text-rose-600 dark:text-rose-400">{{ number_format($record->deductions, 0, ',', ' ') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-800 dark:border-gray-200 font-bold text-base">
                <td class="py-3 text-amber-700 dark:text-amber-400">NET À PAYER</td>
                <td colspan="2" class="py-3 text-right text-xl text-amber-700 dark:text-amber-400">
                    {{ number_format($record->netSalary, 0, ',', ' ') }} FCFA
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Payment History -->
    @if($record->salaryPayments->count() > 0)
    <div class="mb-4 bg-gray-50 dark:bg-gray-800/40 p-3 rounded-lg border border-gray-200 dark:border-gray-700 text-xs">
        <span class="font-bold uppercase text-gray-600 dark:text-gray-400 block mb-1">Règlements enregistrés</span>
        <div class="space-y-1">
            @foreach($record->salaryPayments as $payment)
            <div class="flex justify-between text-gray-700 dark:text-gray-300">
                <span>{{ \Carbon\Carbon::parse($payment->date)->format('d/m/Y') }} — {{ match($payment->method) {
                    'cash' => 'Espèces',
                    'tmoney' => 'TMoney',
                    'flooz' => 'Flooz',
                    'transfer' => 'Virement',
                    default => $payment->method
                } }} @if($payment->notes) ({{ $payment->notes }}) @endif</span>
                <span class="font-semibold">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</span>
            </div>
            @endforeach
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-1 flex justify-between font-bold">
            <span>Total réglé : {{ number_format($record->total_paid, 0, ',', ' ') }} FCFA</span>
            <span class="{{ $record->remaining_amount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                Reste à payer : {{ number_format($record->remaining_amount, 0, ',', ' ') }} FCFA
            </span>
        </div>
    </div>
    @endif

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-8 pt-6 border-t border-gray-200 dark:border-gray-800 text-xs">
        <div class="text-center">
            <p class="font-bold text-gray-500 mb-8">Signature de l'Employé</p>
            <div class="border-b border-dashed border-gray-400 w-3/4 mx-auto"></div>
        </div>
        <div class="text-center">
            <p class="font-bold text-gray-500 mb-8">Cachet & Signature Direction</p>
            <div class="border-b border-dashed border-gray-400 w-3/4 mx-auto"></div>
        </div>
    </div>
</div>
