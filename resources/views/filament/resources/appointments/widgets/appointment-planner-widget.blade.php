<x-filament-widgets::widget>
    @php
        $currentCarbon = \Illuminate\Support\Carbon::parse($currentDate);

        $daysFr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $monthsFr = ['January' => 'Janvier', 'February' => 'Février', 'March' => 'Mars', 'April' => 'Avril', 'May' => 'Mai', 'June' => 'Juin', 'July' => 'Juillet', 'August' => 'Août', 'September' => 'Septembre', 'October' => 'Octobre', 'November' => 'Novembre', 'December' => 'Décembre'];

        $dayName = $daysFr[$currentCarbon->format('l')] ?? $currentCarbon->format('l');
        $monthName = $monthsFr[$currentCarbon->format('F')] ?? $currentCarbon->format('F');

        if ($viewMode === 'day') {
            $periodLabel = "{$dayName} {$currentCarbon->format('j')} {$monthName} {$currentCarbon->format('Y')}";
        } elseif ($viewMode === 'week') {
            $startW = $currentCarbon->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
            $endW = $currentCarbon->copy()->endOfWeek(\Illuminate\Support\Carbon::SUNDAY);
            $periodLabel = "{$startW->format('j')} {$monthsFr[$startW->format('F')]} — {$endW->format('j')} {$monthsFr[$endW->format('F')]} {$endW->format('Y')}";
        } else {
            $periodLabel = "{$monthName} {$currentCarbon->format('Y')}";
        }

        // Determine min and max hour for day view from Settings
        $defaultOpeningSetting = \App\Models\Setting::get('default_opening_time', \App\Models\Setting::get('business_hours_start', '08:00'));
        $defaultClosingSetting = \App\Models\Setting::get('default_closing_time', \App\Models\Setting::get('business_hours_end', '20:00'));

        $minH = (int) explode(':', $defaultOpeningSetting)[0];
        $maxH = (int) explode(':', $defaultClosingSetting)[0];
        if ($maxH <= $minH) {
            $maxH = max(20, $minH + 8);
        }

        if ($viewMode === 'day' && count($appointments) > 0) {
            foreach ($appointments as $a) {
                if ($a['date'] === $currentDate) {
                    $hStart = (int) explode(':', $a['start_time'])[0];
                    $hEnd = (int) explode(':', $a['end_time'])[0];
                    if ($hStart > 0 && $hStart < $minH) $minH = $hStart;
                    if ($hEnd > $maxH && $hEnd <= 23) $maxH = $hEnd;
                }
            }
        }

        $hours = [];
        for ($h = $minH; $h <= $maxH; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        $filteredBarbers = $selectedBarberId ? array_filter($barbers, fn($b) => (string)$b['id'] === (string)$selectedBarberId) : $barbers;
    @endphp

    <div class="space-y-4" wire:poll.5s="loadData">
        <!-- 1. Header & KPI Counter Badges -->
        <x-filament::section compact>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-calendar-days class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-950 dark:text-white">Planning & Agenda</h3>
                            <x-filament::badge color="warning" size="xs">
                                {{ count($appointments) }} rendez-vous
                            </x-filament::badge>
                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                En direct
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Disponibilités, réservations et créneaux des coiffeurs
                        </p>
                    </div>
                </div>

                <!-- Status Counter Badges -->
                <div class="flex items-center gap-2 flex-wrap">
                    <x-filament::badge color="gray" icon="heroicon-m-calendar" size="sm">
                        Aujourd'hui: {{ $todayCount }}
                    </x-filament::badge>
                    <x-filament::badge color="warning" icon="heroicon-m-clock" size="sm">
                        En attente: {{ $pendingCount }}
                    </x-filament::badge>
                    <x-filament::badge color="info" icon="heroicon-m-play" size="sm">
                        En cours: {{ $inProgressCount }}
                    </x-filament::badge>
                    <x-filament::badge color="success" icon="heroicon-m-check" size="sm">
                        Terminés: {{ $completedCount }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        <!-- 2. Navigation & Controls Bar -->
        <x-filament::section compact>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Date Navigation -->
                <div class="flex items-center gap-2 flex-wrap">
                    <x-filament::button wire:click="goToday" size="xs" color="gray">
                        Aujourd'hui
                    </x-filament::button>
                    <div class="flex items-center gap-1">
                        <x-filament::icon-button wire:click="goPrev" icon="heroicon-m-chevron-left" size="sm" color="gray" label="Précédent" />
                        <span class="text-sm font-bold text-gray-900 dark:text-white capitalize px-3 min-w-[180px] text-center">
                            {{ $periodLabel }}
                        </span>
                        <x-filament::icon-button wire:click="goNext" icon="heroicon-m-chevron-right" size="sm" color="gray" label="Suivant" />
                    </div>

                    <!-- Date picker input for direct jumps -->
                    <div class="flex items-center">
                        <input
                            type="date"
                            wire:model.live="currentDate"
                            class="text-xs py-1 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-2xs focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            title="Choisir une date précise"
                        />
                    </div>
                </div>

                <!-- View Mode Switcher -->
                <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1 shrink-0">
                    <button
                        type="button"
                        wire:click="setViewMode('day')"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition-all {{ $viewMode === 'day' ? 'bg-white dark:bg-gray-900 text-amber-600 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        Jour
                    </button>
                    <button
                        type="button"
                        wire:click="setViewMode('week')"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition-all {{ $viewMode === 'week' ? 'bg-white dark:bg-gray-900 text-amber-600 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        Semaine
                    </button>
                    <button
                        type="button"
                        wire:click="setViewMode('month')"
                        class="px-3 py-1 text-xs font-semibold rounded-md transition-all {{ $viewMode === 'month' ? 'bg-white dark:bg-gray-900 text-amber-600 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        Mois
                    </button>
                </div>
            </div>
        </x-filament::section>

        <!-- 3. Barber Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button
                type="button"
                wire:click="filterBarber(null)"
                class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all shrink-0 {{ !$selectedBarberId ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}"
            >
                Tous les coiffeurs
            </button>
            @foreach($barbers as $barber)
                <button
                    type="button"
                    wire:key="filter-barber-{{ $barber['id'] }}"
                    wire:click="filterBarber('{{ $barber['id'] }}')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ (string)$selectedBarberId === (string)$barber['id'] ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}"
                >
                    <span class="h-1.5 w-1.5 rounded-full {{ (string)$selectedBarberId === (string)$barber['id'] ? 'bg-white' : 'bg-amber-500' }}"></span>
                    {{ $barber['name'] }}
                </button>
            @endforeach
        </div>

        <!-- Helpful notification when day has 0 RDV but week has RDV -->
        @if($viewMode === 'day' && count($appointments) === 0 && $weekCount > 0)
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-information-circle class="h-4 w-4 shrink-0 text-amber-600" />
                    <span>Aucun rendez-vous sur cette journée, mais <strong>{{ $weekCount }} rendez-vous</strong> existent sur cette semaine.</span>
                </div>
                <button
                    type="button"
                    wire:click="setViewMode('week')"
                    class="px-2.5 py-1 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition-colors shrink-0"
                >
                    Voir la semaine
                </button>
            </div>
        @endif

        <!-- 4. VUE JOUR (Multi-column Barber Agenda) -->
        @if($viewMode === 'day')
            <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-xs overflow-x-auto">
                <div class="min-w-[720px]">
                    <!-- Table Header: Barber columns -->
                    <div class="grid border-b border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/50" style="grid-template-columns: 80px repeat({{ max(1, count($filteredBarbers)) }}, 1fr);">
                        <div class="p-3 text-xs font-bold text-gray-400 text-center border-r border-gray-200 dark:border-gray-800">
                            Heure
                        </div>
                        @forelse($filteredBarbers as $barber)
                            <div wire:key="barber-col-{{ $barber['id'] }}" class="p-3 text-xs font-bold text-gray-900 dark:text-white text-center border-r border-gray-200 dark:border-gray-800 last:border-r-0 flex items-center justify-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                {{ $barber['name'] }}
                            </div>
                        @empty
                            <div class="p-3 text-xs font-medium text-gray-500 text-center">
                                Aucun coiffeur disponible
                            </div>
                        @endforelse
                    </div>

                    <!-- Hourly Rows -->
                    <div class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        @foreach($hours as $hour)
                            @php
                                $slotInt = (int) explode(':', $hour)[0];
                            @endphp
                            <div wire:key="hour-row-{{ $hour }}" class="grid min-h-[72px]" style="grid-template-columns: 80px repeat({{ max(1, count($filteredBarbers)) }}, 1fr);">
                                <!-- Time Label -->
                                <div class="p-2 text-xs font-semibold text-gray-400 text-center border-r border-gray-200 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20 flex items-center justify-center">
                                    {{ $hour }}
                                </div>

                                <!-- Barber Cells -->
                                @foreach($filteredBarbers as $barber)
                                    @php
                                        $cellAppts = array_filter($appointments, function($a) use ($barber, $slotInt, $currentDate) {
                                            if ((string) $a['barber_id'] !== (string) $barber['id']) return false;
                                            if ($a['date'] !== $currentDate) return false;
                                            $apptHour = (int) explode(':', $a['start_time'])[0];
                                            return $apptHour === $slotInt;
                                        });
                                    @endphp

                                    <div wire:key="cell-{{ $barber['id'] }}-{{ $hour }}" class="p-1.5 border-r border-gray-200 dark:border-gray-800 last:border-r-0 relative group hover:bg-amber-50/30 dark:hover:bg-amber-950/10 transition-colors">
                                        @if(count($cellAppts) > 0)
                                            @foreach($cellAppts as $appt)
                                                @php
                                                    $cardBg = match($appt['status']) {
                                                        'confirmed' => 'bg-sky-50 dark:bg-sky-950/50 border-sky-300 text-sky-950 dark:text-sky-200 border-l-4 border-l-sky-500',
                                                        'in_progress' => 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-300 text-emerald-950 dark:text-emerald-200 border-l-4 border-l-emerald-500',
                                                        'completed' => 'bg-emerald-100 dark:bg-emerald-900/60 border-emerald-400 text-emerald-950 dark:text-emerald-100 border-l-4 border-l-emerald-700',
                                                        'cancelled', 'no_show' => 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 text-rose-600 border-l-4 border-l-rose-500 line-through',
                                                        default => 'bg-amber-50 dark:bg-amber-950/50 border-amber-300 text-amber-950 dark:text-amber-200 border-l-4 border-l-amber-500',
                                                    };
                                                @endphp
                                                <div wire:key="appt-card-{{ $appt['id'] }}" class="rounded-lg p-2 border text-xs shadow-2xs mb-1.5 last:mb-0 transition-all hover:scale-[1.01] {{ $cardBg }}">
                                                    <div class="flex items-center justify-between font-bold">
                                                        <span class="truncate">{{ $appt['client_name'] }}</span>
                                                        <span class="text-[10px] tabular-nums opacity-80 shrink-0 ml-1">{{ $appt['start_time'] }} - {{ $appt['end_time'] }}</span>
                                                    </div>
                                                    <div class="text-[11px] opacity-90 truncate mt-0.5">
                                                        ✂️ {{ $appt['service_name'] }}
                                                    </div>
                                                    @if(!empty($appt['client_phone']))
                                                        <div class="text-[10px] opacity-75 truncate mt-0.5">
                                                            📞 {{ $appt['client_phone'] }}
                                                        </div>
                                                    @endif

                                                    <!-- Status Quick Actions Buttons -->
                                                    <div class="mt-1.5 pt-1 border-t border-black/10 dark:border-white/10 flex items-center justify-end gap-1 flex-wrap">
                                                        @if($appt['status'] === 'pending')
                                                            <button
                                                                type="button"
                                                                wire:click="updateStatus({{ $appt['id'] }}, 'confirmed')"
                                                                class="text-[9px] px-1.5 py-0.5 rounded bg-sky-600 text-white font-semibold hover:bg-sky-700 transition-colors"
                                                            >
                                                                Confirmer
                                                            </button>
                                                        @elseif($appt['status'] === 'confirmed')
                                                            <button
                                                                type="button"
                                                                wire:click="updateStatus({{ $appt['id'] }}, 'in_progress')"
                                                                class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors"
                                                            >
                                                                Démarrer
                                                            </button>
                                                        @elseif($appt['status'] === 'in_progress')
                                                            <button
                                                                type="button"
                                                                wire:click="updateStatus({{ $appt['id'] }}, 'completed')"
                                                                class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-700 text-white font-semibold hover:bg-emerald-800 transition-colors"
                                                            >
                                                                Terminer
                                                            </button>
                                                        @endif

                                                        @if(!in_array($appt['status'], ['completed', 'cancelled', 'no_show']))
                                                            <button
                                                                type="button"
                                                                wire:click="updateStatus({{ $appt['id'] }}, 'cancelled')"
                                                                class="text-[9px] px-1.5 py-0.5 rounded bg-rose-600 text-white font-semibold hover:bg-rose-700 transition-colors"
                                                            >
                                                                Annuler
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <!-- Empty Slot Click Target -->
                                            <div class="w-full h-full min-h-[50px] flex items-center justify-center rounded-lg border border-dashed border-transparent group-hover:border-amber-300 dark:group-hover:border-amber-700 text-gray-300 group-hover:text-amber-600 transition-all text-xs font-medium">
                                                <span class="opacity-0 group-hover:opacity-100 flex items-center gap-1">
                                                    <x-heroicon-m-clock class="h-3.5 w-3.5" /> Libre
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif($viewMode === 'week')
            <!-- VUE SEMAINE (7 Day Grid) -->
            @php
                $weekDays = [];
                $wStart = \Illuminate\Support\Carbon::parse($currentDate)->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
                for ($d = 0; $d < 7; $d++) {
                    $dayObj = $wStart->copy()->addDays($d);
                    $weekDays[] = [
                        'date' => $dayObj->toDateString(),
                        'day_num' => $dayObj->format('j'),
                        'day_name' => $daysFr[$dayObj->format('l')] ?? $dayObj->format('l'),
                        'is_today' => $dayObj->isToday(),
                        'is_selected' => $dayObj->toDateString() === $currentDate,
                    ];
                }
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-7 gap-3">
                @foreach($weekDays as $wDay)
                    @php
                        $dayAppts = array_filter($appointments, fn($a) => $a['date'] === $wDay['date']);
                    @endphp
                    <div
                        wire:key="week-day-{{ $wDay['date'] }}"
                        class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-3 shadow-xs flex flex-col h-full transition-all hover:border-amber-400 dark:hover:border-amber-600 {{ $wDay['is_today'] ? 'ring-2 ring-amber-500/50' : '' }}"
                    >
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100 dark:border-gray-800 {{ $wDay['is_today'] ? 'text-amber-600 dark:text-amber-400 font-bold' : '' }}">
                            <div>
                                <span class="text-xs uppercase text-gray-400 block">{{ substr($wDay['day_name'], 0, 3) }}</span>
                                <span class="text-lg font-extrabold">{{ $wDay['day_num'] }}</span>
                            </div>
                            <button
                                type="button"
                                wire:click="setDate('{{ $wDay['date'] }}'); setViewMode('day');"
                                class="text-[10px] px-2 py-0.5 rounded-md bg-gray-100 hover:bg-amber-100 dark:bg-gray-800 dark:hover:bg-amber-950/60 text-gray-600 dark:text-gray-300 hover:text-amber-700 dark:hover:text-amber-300 font-medium transition-colors"
                                title="Voir la journée en détail"
                            >
                                Détails
                            </button>
                        </div>

                        <div class="space-y-2 flex-1 overflow-y-auto max-h-96">
                            @forelse($dayAppts as $appt)
                                @php
                                    $wCardBg = match($appt['status']) {
                                        'confirmed' => 'bg-sky-50/70 dark:bg-sky-950/40 border-sky-200 dark:border-sky-800',
                                        'in_progress' => 'bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800',
                                        'completed' => 'bg-emerald-100/70 dark:bg-emerald-900/40 border-emerald-300 dark:border-emerald-700',
                                        'cancelled', 'no_show' => 'bg-rose-50/70 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 line-through opacity-70',
                                        default => 'bg-amber-50/70 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800',
                                    };
                                @endphp
                                <div
                                    wire:key="week-appt-{{ $appt['id'] }}"
                                    wire:click="setDate('{{ $wDay['date'] }}'); setViewMode('day');"
                                    class="rounded-lg p-2 border text-xs cursor-pointer transition-all hover:scale-[1.02] {{ $wCardBg }}"
                                >
                                    <div class="font-bold text-gray-900 dark:text-white truncate">{{ $appt['client_name'] }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $appt['start_time'] }} • {{ $appt['service_name'] }}</div>
                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 font-medium truncate mt-0.5">💈 {{ $appt['barber_name'] }}</div>
                                </div>
                            @empty
                                <div class="text-center text-gray-300 dark:text-gray-600 text-[11px] py-4">Aucun RDV</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- VUE MOIS (Month Grid) -->
            <x-filament::section>
                <div class="text-center py-6">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-3">
                        <x-heroicon-o-calendar class="h-6 w-6" />
                    </div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Aperçu mensuel ({{ $periodLabel }})</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                        <strong>{{ count($appointments) }} rendez-vous</strong> sont enregistrés sur l'ensemble du mois.
                    </p>

                    @if(count($appointments) > 0)
                        <div class="mt-4 max-w-lg mx-auto text-left divide-y divide-gray-100 dark:divide-gray-800 border rounded-xl overflow-hidden bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800">
                            @foreach(array_slice($appointments, 0, 8) as $mAppt)
                                <div
                                    wire:key="month-appt-{{ $mAppt['id'] }}"
                                    wire:click="setDate('{{ $mAppt['date'] }}'); setViewMode('day');"
                                    class="p-2.5 flex items-center justify-between gap-3 text-xs hover:bg-amber-50/50 dark:hover:bg-amber-950/20 cursor-pointer transition-colors"
                                >
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $mAppt['client_name'] }}</span>
                                        <span class="text-gray-500 text-[11px]"> ({{ $mAppt['service_name'] }})</span>
                                        <div class="text-[10px] text-gray-400">💈 {{ $mAppt['barber_name'] }}</div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">{{ \Carbon\Carbon::parse($mAppt['date'])->format('d/m') }} à {{ $mAppt['start_time'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 flex justify-center gap-2">
                        <x-filament::button wire:click="setViewMode('day')" size="sm" color="amber">
                            Basculer en Vue Jour (Agenda)
                        </x-filament::button>
                        <x-filament::button wire:click="setViewMode('week')" size="sm" color="gray">
                            Vue Semaine
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>

