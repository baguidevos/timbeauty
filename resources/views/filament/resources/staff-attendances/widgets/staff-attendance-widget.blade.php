<x-filament-widgets::widget>
    @php
        $daysFr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $monthsFr = ['January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'];
        $carbon = \Carbon\Carbon::parse($currentDate);
        $dateFormatted = ($daysFr[$carbon->format('l')] ?? $carbon->format('l')) . ' ' . $carbon->format('j') . ' ' . ($monthsFr[$carbon->format('F')] ?? $carbon->format('F')) . ' ' . $carbon->format('Y');
    @endphp

    <div class="space-y-5">
        <!-- 1. En-tête original avec Section & Badges KPIs -->
        <x-filament::section compact>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-finger-print class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-950 dark:text-white">Pointage & Présence du Jour</h3>
                            <x-filament::badge color="warning" size="xs" class="p-1">
                                {{ $dateFormatted }}
                            </x-filament::badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Enregistrement en direct des arrivées, départs et durées de travail
                        </p>
                    </div>
                </div>

                <!-- KPI Badges -->
                <div class="flex items-center gap-2 flex-wrap">
                    <x-filament::badge class="p-1" color="{{ $attendanceRate >= 80 ? 'success' : ($attendanceRate >= 50 ? 'warning' : 'danger') }}" icon="heroicon-m-chart-bar" size="sm">
                        Taux de présence : {{ $attendanceRate }}%
                    </x-filament::badge>
                    <x-filament::badge class="p-1" color="success" icon="heroicon-m-check-circle" size="sm">
                        Présents : {{ $presentCount }}
                    </x-filament::badge>
                    <x-filament::badge class="p-1" color="warning" icon="heroicon-m-clock" size="sm">
                        En retard : {{ $lateCount }}
                    </x-filament::badge>
                    <x-filament::badge class="p-1" color="danger" icon="heroicon-m-user-minus" size="sm">
                        Absents : {{ $absentCount }}
                    </x-filament::badge>
                    <x-filament::badge class="p-1" color="gray" icon="heroicon-m-users" size="sm">
                        Effectif : {{ $totalStaff }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        <!-- 2. Horloge Numérique en Direct (Live Clock) -->
        <div class="grid gap-4 sm:grid-cols-[1fr_auto] items-center">
            <div
                x-data="{
                    time: '',
                    dateStr: '',
                    init() {
                        const update = () => {
                            const now = new Date();
                            this.time = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                            this.dateStr = now.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                        };
                        update();
                        setInterval(update, 1000);
                    }
                }"
                class="flex flex-col items-center justify-center py-5 px-4 rounded-2xl bg-gradient-to-br from-amber-500/15 via-amber-500/5 to-transparent border border-amber-500/25 shadow-xs"
            >
                <div class="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-amber-600 dark:text-amber-400 font-bold mb-1">
                    <x-heroicon-o-clock class="h-4 w-4 animate-pulse" />
                    Heure actuelle
                </div>
                <div class="text-3xl sm:text-4xl font-extrabold tabular-nums tracking-tight text-gray-950 dark:text-white" x-text="time">
                    --:--:--
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 capitalize tabular-nums font-medium" x-text="dateStr">
                    {{ $dateFormatted }}
                </div>
            </div>

            <div class="flex sm:flex-col justify-end gap-2">
                <x-filament::button
                    wire:click="loadData"
                    color="gray"
                    icon="heroicon-o-arrow-path"
                    class="h-9"
                >
                    Actualiser
                </x-filament::button>
            </div>
        </div>

        <!-- 3. Grille des Cartes Employés avec Toutes les Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($staffRecords as $staff)
                @php
                    $statusColor = match($staff['status']) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'half_day' => 'info',
                        default => 'gray',
                    };
                    $statusLabel = match($staff['status']) {
                        'present' => 'Présent',
                        'late' => 'En retard',
                        'absent' => 'Absent',
                        'half_day' => 'Demi-journée',
                        default => 'Programmé',
                    };
                    $statusIcon = match($staff['status']) {
                        'present' => 'heroicon-m-check-circle',
                        'late' => 'heroicon-m-clock',
                        'absent' => 'heroicon-m-x-circle',
                        'half_day' => 'heroicon-m-sun',
                        default => 'heroicon-m-calendar',
                    };
                    $initials = collect(explode(' ', $staff['barber_name']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                @endphp

                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xs hover:border-amber-500/30 hover:shadow-md transition-all flex flex-col justify-between gap-3.5">
                    <div>
                        <!-- Header: Avatar + Pulse badge + Name + Status Pill -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="relative shrink-0">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white font-bold text-xs shadow-xs">
                                        {{ $initials }}
                                    </div>
                                    @if($staff['is_clocked_in'])
                                        <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex h-3.5 w-3.5 rounded-full border-2 border-white dark:border-gray-900 bg-emerald-500"></span>
                                        </span>
                                    @elseif($staff['status'] === 'late' && !$staff['is_clocked_in'])
                                        <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex h-3.5 w-3.5 rounded-full border-2 border-white dark:border-gray-900 bg-amber-500"></span>
                                        </span>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h4 class="text-sm font-bold text-gray-950 dark:text-white truncate">
                                            {{ $staff['barber_name'] }}
                                        </h4>
                                        @if(($staff['barber_status'] ?? '') === 'on_leave')
                                            <x-filament::badge class="p-1" color="warning" size="xs">
                                                En congé
                                            </x-filament::badge>
                                        @elseif(($staff['barber_status'] ?? '') === 'inactive')
                                            <x-filament::badge class="p-1" color="danger" size="xs">
                                                Inactif
                                            </x-filament::badge>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        {{ $staff['job_title'] }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::badge class="p-1" :color="$statusColor" :icon="$statusIcon" size="xs">
                                {{ $statusLabel }}
                            </x-filament::badge>
                        </div>

                        <!-- 3 Metrics Boxes (Entrée, Sortie, Durée) -->
                        <div class="grid grid-cols-3 gap-2 mt-3.5 text-center">
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-2 border border-gray-100 dark:border-gray-800/60">
                                <p class="text-[10px] uppercase font-semibold text-gray-400">Entrée</p>
                                <p class="text-xs font-bold text-gray-900 dark:text-gray-100 mt-0.5 tabular-nums">
                                    {{ $staff['clock_in'] ?? '—' }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-2 border border-gray-100 dark:border-gray-800/60">
                                <p class="text-[10px] uppercase font-semibold text-gray-400">Sortie</p>
                                <p class="text-xs font-bold text-gray-900 dark:text-gray-100 mt-0.5 tabular-nums">
                                    {{ $staff['clock_out'] ?? '—' }}
                                </p>
                            </div>
                            <div class="rounded-xl p-2 border {{ $staff['is_clocked_in'] ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800/50' : 'bg-gray-50 dark:bg-gray-800/50 border-gray-100 dark:border-gray-800/60' }}">
                                <p class="text-[10px] uppercase font-semibold text-gray-400">Durée</p>
                                <p class="text-xs font-bold mt-0.5 tabular-nums {{ $staff['is_clocked_in'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $staff['worked_hours'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Row 1: Pointer l'entrée / Pointer la sortie -->
                    <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="clockIn({{ $staff['barber_id'] }})"
                                @disabled($staff['is_clocked_in'] || $staff['status'] === 'absent')
                                class="flex-1 inline-flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg text-xs font-semibold transition-all shadow-2xs {{ ($staff['is_clocked_in'] || $staff['status'] === 'absent') ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}"
                            >
                                <x-heroicon-m-arrow-right-on-rectangle class="h-3.5 w-3.5" />
                                Pointer l'entrée
                            </button>

                            <button
                                type="button"
                                wire:click="clockOut({{ $staff['barber_id'] }})"
                                @disabled(!$staff['is_clocked_in'] || $staff['is_clocked_out'])
                                class="flex-1 inline-flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg text-xs font-semibold transition-all shadow-2xs {{ (!$staff['is_clocked_in'] || $staff['is_clocked_out']) ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white' }}"
                            >
                                <x-heroicon-m-arrow-left-on-rectangle class="h-3.5 w-3.5" />
                                Pointer la sortie
                            </button>
                        </div>

                        <!-- Action Buttons Row 2: Marquer absent + Statut Select shortcut -->
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="markAbsent({{ $staff['barber_id'] }})"
                                @disabled($staff['is_clocked_in'] || $staff['status'] === 'absent')
                                class="flex-1 inline-flex items-center justify-center gap-1.5 h-7 px-2 rounded-lg text-[11px] font-medium border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors {{ ($staff['is_clocked_in'] || $staff['status'] === 'absent') ? 'opacity-50 cursor-not-allowed' : '' }}"
                            >
                                <x-heroicon-m-user-minus class="h-3 w-3" />
                                Marquer absent
                            </button>

                            <select
                                wire:change="setStatus({{ $staff['barber_id'] }}, $event.target.value)"
                                class="h-7 text-[11px] font-medium rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 flex-1 min-w-[100px] py-0 px-2"
                            >
                                <option value="scheduled" @selected($staff['status'] === 'scheduled')>Programmé</option>
                                <option value="present" @selected($staff['status'] === 'present')>Présent</option>
                                <option value="late" @selected($staff['status'] === 'late')>En retard</option>
                                <option value="absent" @selected($staff['status'] === 'absent')>Absent</option>
                                <option value="half_day" @selected($staff['status'] === 'half_day')>Demi-journée</option>
                            </select>
                        </div>

                        <!-- Action Row 3: Live Notes Input on blur -->
                        <div x-data="{ notes: '{{ addslashes($staff['notes']) }}' }">
                            <input
                                type="text"
                                x-model="notes"
                                @blur="$wire.updateNotes({{ $staff['barber_id'] }}, notes)"
                                placeholder="Notes (optionnel)..."
                                class="w-full h-7 px-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-[11px] text-gray-700 dark:text-gray-300 placeholder-gray-400 focus:bg-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                            />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
