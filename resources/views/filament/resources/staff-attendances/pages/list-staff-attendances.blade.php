<x-filament-panels::page>
    <div class="space-y-6">
        <!-- 1. Main Navigation Tabs (Aujourd'hui / Historique) -->
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$activeTab === 'today'"
                wire:click="$set('activeTab', 'today')"
                icon="heroicon-o-clock"
            >
                Aujourd'hui (Pointage)
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'history'"
                wire:click="$set('activeTab', 'history')"
                icon="heroicon-o-calendar-days"
            >
                Historique & Récapitulatif
            </x-filament::tabs.item>
        </x-filament::tabs>

        <!-- ========================================================================= -->
        <!-- ONGLET 1 : AUJOURD'HUI (POINTAGE EN DIRECT - WIDGET DU JOUR)              -->
        <!-- ========================================================================= -->
        @if($activeTab === 'today')
            @livewire(\App\Filament\Resources\StaffAttendances\Widgets\StaffAttendanceWidget::class)
        @else
            <!-- ========================================================================= -->
            <!-- ONGLET 2 : HISTORIQUE & RÉCAPITULATIF                                     -->
            <!-- ========================================================================= -->
            @php
                $totals = $this->historyTotals;
                $summaries = $this->historySummaries;
                $records = $this->historyRecords;
                $barbers = $this->barbersList;
            @endphp

            <div class="space-y-6">
                <!-- Filter Controls Bar -->
                <x-filament::section compact>
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Period Presets -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1">Période :</span>
                            <button
                                type="button"
                                wire:click="applyPreset(7)"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg border transition-colors {{ $this->isPresetActive(7) ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100' }}"
                            >
                                7 jours
                            </button>
                            <button
                                type="button"
                                wire:click="applyPreset(14)"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg border transition-colors {{ $this->isPresetActive(14) ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100' }}"
                            >
                                14 jours
                            </button>
                            <button
                                type="button"
                                wire:click="applyPreset(30)"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg border transition-colors {{ $this->isPresetActive(30) ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100' }}"
                            >
                                30 jours
                            </button>
                            <button
                                type="button"
                                wire:click="applyPreset(90)"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg border transition-colors {{ $this->isPresetActive(90) ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100' }}"
                            >
                                90 jours
                            </button>
                        </div>

                        <!-- Date inputs and Selects -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-gray-400">Du</span>
                                <input
                                    type="date"
                                    wire:model.live="historyFrom"
                                    class="h-8 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-medium text-gray-900 dark:text-gray-100 shadow-xs focus:border-amber-500 focus:ring-amber-500"
                                />
                                <span class="text-xs text-gray-400">Au</span>
                                <input
                                    type="date"
                                    wire:model.live="historyTo"
                                    class="h-8 rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-medium text-gray-900 dark:text-gray-100 shadow-xs focus:border-amber-500 focus:ring-amber-500"
                                />
                            </div>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="historyBarberId" class="h-8 text-xs">
                                    <option value="all">Tous les employés</option>
                                    @foreach($barbers as $b)
                                        <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="historyStatus" class="h-8 text-xs">
                                    <option value="all">Tous les statuts</option>
                                    <option value="present">Présent</option>
                                    <option value="late">En retard</option>
                                    <option value="absent">Absent</option>
                                    <option value="half_day">Demi-journée</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </x-filament::section>

                <!-- Period Summary KPI Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-filament::section compact>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                <x-heroicon-o-clock class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Heures travaillées</p>
                                <p class="text-lg font-bold text-gray-950 dark:text-white">{{ $totals['total_hours_formatted'] }}</p>
                            </div>
                        </div>
                    </x-filament::section>

                    <x-filament::section compact>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-o-chart-bar class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Taux d'assiduité</p>
                                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $totals['avg_attendance_rate'] }}%</p>
                            </div>
                        </div>
                    </x-filament::section>

                    <x-filament::section compact>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                <x-heroicon-o-check-circle class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Jours de présence</p>
                                <p class="text-lg font-bold text-gray-950 dark:text-white">{{ $totals['total_present_days'] }}</p>
                            </div>
                        </div>
                    </x-filament::section>

                    <x-filament::section compact>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                <x-heroicon-o-user-minus class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Retards & Absences</p>
                                <p class="text-lg font-bold text-gray-950 dark:text-white">{{ $totals['total_late_days'] }} retards • {{ $totals['total_absent_days'] }} abs.</p>
                            </div>
                        </div>
                    </x-filament::section>
                </div>

                <!-- Summary by Employee Table -->
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-users class="h-5 w-5 text-amber-500" />
                            <span>Synthèse par Employé sur la période</span>
                        </div>
                    </x-slot>

                    <div class="overflow-x-auto -mx-6 -mb-6">
                        <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50/75 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="px-6 py-3">Employé</th>
                                    <th class="px-4 py-3 text-center">Jours Présent</th>
                                    <th class="px-4 py-3 text-center">En retard</th>
                                    <th class="px-4 py-3 text-center">Absences</th>
                                    <th class="px-4 py-3 text-center">Demi-journées</th>
                                    <th class="px-4 py-3 text-right">Total Travaillé</th>
                                    <th class="px-6 py-3 text-right">Taux de présence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                @forelse($summaries as $sum)
                                    <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/10 transition-colors">
                                        <td class="px-6 py-3 font-semibold text-gray-950 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <div class="h-7 w-7 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-[10px]">
                                                    {{ substr($sum['name'], 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold">{{ $sum['name'] }}</p>
                                                    <p class="text-[10px] text-gray-400 font-normal">{{ $sum['job_title'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-emerald-600 dark:text-emerald-400">{{ $sum['days_present'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-amber-600 dark:text-amber-400">{{ $sum['days_late'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-rose-600 dark:text-rose-400">{{ $sum['days_absent'] }}</td>
                                        <td class="px-4 py-3 text-center text-gray-500">{{ $sum['days_half_day'] }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-gray-100">{{ $sum['total_hours_formatted'] }}</td>
                                        <td class="px-6 py-3 text-right">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $sum['attendance_rate'] >= 80 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : ($sum['attendance_rate'] >= 50 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300') }}">
                                                {{ $sum['attendance_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-6 text-center text-gray-400">Aucun pointage sur cette période.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>

                <!-- Detailed Records Table -->
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-document-text class="h-5 w-5 text-amber-500" />
                                <span>Registre détaillé des pointages</span>
                            </div>
                            <span class="text-xs text-gray-400 font-normal">{{ count($records) }} enregistrement(s)</span>
                        </div>
                    </x-slot>

                    <div class="overflow-x-auto -mx-6 -mb-6">
                        <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50/75 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-4 py-3">Employé</th>
                                    <th class="px-4 py-3 text-center">Arrivée</th>
                                    <th class="px-4 py-3 text-center">Départ</th>
                                    <th class="px-4 py-3 text-center">Pause</th>
                                    <th class="px-4 py-3 text-center">Durée</th>
                                    <th class="px-4 py-3 text-center">Statut</th>
                                    <th class="px-4 py-3">Notes</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                @forelse($records as $rec)
                                    @php
                                        $badgeColor = match($rec->status) {
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger',
                                            'half_day' => 'info',
                                            default => 'gray',
                                        };
                                        $badgeLabel = match($rec->status) {
                                            'present' => 'Présent',
                                            'late' => 'En retard',
                                            'absent' => 'Absent',
                                            'half_day' => 'Demi-journée',
                                            default => $rec->status,
                                        };
                                    @endphp
                                    <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/10 transition-colors">
                                        <td class="px-6 py-3 font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($rec->date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $rec->barber ? "{$rec->barber->firstName} {$rec->barber->lastName}" : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $rec->clockIn ? \Carbon\Carbon::parse($rec->clockIn)->format('H:i') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ $rec->clockOut ? \Carbon\Carbon::parse($rec->clockOut)->format('H:i') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-500 whitespace-nowrap">
                                            {{ $rec->breakMinutes ? "{$rec->breakMinutes}m" : '0m' }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap">
                                            {{ $rec->worked_hours_formatted }}
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <x-filament::badge :color="$badgeColor" size="xs">
                                                {{ $badgeLabel }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 truncate max-w-xs">
                                            {{ $rec->notes ?: '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1">
                                                {{ ($this->editAttendanceAction)(['record' => $rec->id]) }}
                                                {{ ($this->deleteAttendanceAction)(['record' => $rec->id]) }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-6 text-center text-gray-400">Aucun pointage trouvé pour ces critères.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
