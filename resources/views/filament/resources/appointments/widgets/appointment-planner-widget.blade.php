<x-filament-widgets::widget>
    @php
        $currentCarbon = \Illuminate\Support\Carbon::parse($currentDate);

        $daysFr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $monthsFr = ['January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'];

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

        $hours = [];
        for ($h = 8; $h <= 19; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        $filteredBarbers = $selectedBarberId ? array_filter($barbers, fn($b) => $b['id'] == $selectedBarberId) : $barbers;
    @endphp

    <div class="space-y-4">
        <!-- 1. Header & KPI Counter Pills -->
        <div class="glass-card-premium rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-md shadow-amber-500/20 shrink-0">
                    <x-heroicon-o-calendar-days class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                        <span>Planning des Rendez-vous</span>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            {{ count($appointments) }} RDV
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Gestion en temps réel de l'agenda et de la disponibilité des coiffeurs
                    </p>
                </div>
            </div>

            <!-- Status Counter Badges -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                    <span class="h-2 w-2 rounded-full bg-gray-500"></span>
                    Aujourd'hui: <strong>{{ $todayCount }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-300">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    En attente: <strong>{{ $pendingCount }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-sky-50 dark:bg-sky-950/50 px-2.5 py-1 text-xs font-medium text-sky-700 dark:bg-sky-300">
                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                    En cours: <strong>{{ $inProgressCount }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Terminés: <strong>{{ $completedCount }}</strong>
                </span>
            </div>
        </div>

        <!-- 2. Navigation & Controls Bar -->
        <div class="rounded-2xl bg-white dark:bg-gray-900 p-4 border border-gray-200/80 dark:border-gray-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Date Navigation -->
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="goToday" size="xs" color="gray">
                    Aujourd'hui
                </x-filament::button>
                <div class="flex items-center gap-1">
                    <x-filament::icon-button wire:click="goPrev" icon="heroicon-m-chevron-left" size="sm" label="Précédent" />
                    <span class="text-sm font-semibold text-gray-900 dark:text-white capitalize px-2 min-w-[160px] text-center">
                        {{ $periodLabel }}
                    </span>
                    <x-filament::icon-button wire:click="goNext" icon="heroicon-m-chevron-right" size="sm" label="Suivant" />
                </div>
            </div>

            <!-- View Mode Switcher -->
            <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl shrink-0">
                <button wire:click="setViewMode('day')" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'day' ? 'bg-white dark:bg-gray-900 text-amber-600 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    Jour
                </button>
                <button wire:click="setViewMode('week')" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'week' ? 'bg-white dark:bg-gray-900 text-amber-600 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    Semaine
                </button>
                <button wire:click="setViewMode('month')" class="px-3 py-1 text-xs font-semibold rounded-lg transition-all {{ $viewMode === 'month' ? 'bg-white dark:bg-gray-900 text-amber-600 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    Mois
                </button>
            </div>
        </div>

        <!-- 3. Barber Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <button wire:click="filterBarber(null)" class="px-3 py-1 rounded-full text-xs font-medium transition-all shrink-0 {{ !$selectedBarberId ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}">
                Tous les coiffeurs
            </button>
            @foreach($barbers as $barber)
                <button wire:click="filterBarber('{{ $barber['id'] }}')" class="px-3 py-1 rounded-full text-xs font-medium transition-all shrink-0 {{ $selectedBarberId == $barber['id'] ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}">
                    💈 {{ $barber['name'] }}
                </button>
            @endforeach
        </div>

        <!-- 4. VUE JOUR (Multi-column Barber Agenda) -->
        @if($viewMode === 'day')
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 shadow-sm overflow-x-auto">
                <div class="min-w-[700px]">
                    <!-- Table Header: Barber columns -->
                    <div class="grid border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50" style="grid-template-columns: 80px repeat({{ count($filteredBarbers) }}, 1fr);">
                        <div class="p-3 text-xs font-bold text-gray-400 text-center border-r border-gray-200 dark:border-gray-800">
                            Heure
                        </div>
                        @foreach($filteredBarbers as $barber)
                            <div class="p-3 text-xs font-bold text-gray-900 dark:text-white text-center border-r border-gray-200 dark:border-gray-800 last:border-r-0 flex items-center justify-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                {{ $barber['name'] }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Hourly Rows (08:00 to 19:00) -->
                    <div class="divide-y divide-gray-100 dark:divide-gray-800/60">
                        @foreach($hours as $hour)
                            <div class="grid min-h-[70px]" style="grid-template-columns: 80px repeat({{ count($filteredBarbers) }}, 1fr);">
                                <!-- Time Label -->
                                <div class="p-2 text-xs font-semibold text-gray-400 text-center border-r border-gray-200 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20">
                                    {{ $hour }}
                                </div>

                                <!-- Barber Cells -->
                                @foreach($filteredBarbers as $barber)
                                    @php
                                        $cellAppts = array_filter($appointments, function($a) use ($barber, $hour, $currentDate) {
                                            if ($a['barber_id'] != $barber['id']) return false;
                                            if ($a['date'] !== $currentDate) return false;
                                            $apptHour = substr($a['start_time'], 0, 2);
                                            $slotHour = substr($hour, 0, 2);
                                            return $apptHour === $slotHour;
                                        });
                                    @endphp

                                    <div class="p-1.5 border-r border-gray-200 dark:border-gray-800 last:border-r-0 relative group hover:bg-amber-50/30 dark:hover:bg-amber-950/10 transition-colors">
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
                                                <div class="rounded-xl p-2 border text-xs shadow-xs mb-1 last:mb-0 transition-all hover:scale-[1.02] {{ $cardBg }}">
                                                    <div class="flex items-center justify-between font-bold">
                                                        <span class="truncate">{{ $appt['client_name'] }}</span>
                                                        <span class="text-[10px] tabular-nums opacity-80">{{ $appt['start_time'] }} - {{ $appt['end_time'] }}</span>
                                                    </div>
                                                    <div class="text-[11px] opacity-90 truncate mt-0.5">
                                                        ✂️ {{ $appt['service_name'] }}
                                                    </div>

                                                    <!-- Status Quick Actions Buttons -->
                                                    <div class="mt-1.5 pt-1 border-t border-black/10 dark:border-white/10 flex items-center justify-end gap-1">
                                                        @if($appt['status'] === 'pending')
                                                            <button wire:click="updateStatus({{ $appt['id'] }}, 'confirmed')" class="text-[9px] px-1.5 py-0.5 rounded-md bg-sky-600 text-white font-semibold hover:bg-sky-700">
                                                                Confirmer
                                                            </button>
                                                        @elseif($appt['status'] === 'confirmed')
                                                            <button wire:click="updateStatus({{ $appt['id'] }}, 'in_progress')" class="text-[9px] px-1.5 py-0.5 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                                                                Démarrer
                                                            </button>
                                                        @elseif($appt['status'] === 'in_progress')
                                                            <button wire:click="updateStatus({{ $appt['id'] }}, 'completed')" class="text-[9px] px-1.5 py-0.5 rounded-md bg-emerald-700 text-white font-semibold hover:bg-emerald-800">
                                                                Terminer
                                                            </button>
                                                        @endif

                                                        @if(!in_array($appt['status'], ['completed', 'cancelled']))
                                                            <button wire:click="updateStatus({{ $appt['id'] }}, 'cancelled')" class="text-[9px] px-1.5 py-0.5 rounded-md bg-rose-600 text-white font-semibold hover:bg-rose-700">
                                                                Annuler
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <!-- Empty Slot Click Target -->
                                            <a href="/admin/appointments/create?barberId={{ $barber['id'] }}&date={{ $currentDate }}&startTime={{ $hour }}" class="w-full h-full min-h-[50px] flex items-center justify-center rounded-lg border border-dashed border-transparent group-hover:border-amber-300 dark:group-hover:border-amber-700 text-gray-300 group-hover:text-amber-600 transition-all text-xs font-medium">
                                                <span class="opacity-0 group-hover:opacity-100 flex items-center gap-1">
                                                    <x-heroicon-m-plus class="h-3.5 w-3.5" /> Réserver
                                                </span>
                                            </a>
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
                    ];
                }
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                @foreach($weekDays as $wDay)
                    @php
                        $dayAppts = array_filter($appointments, fn($a) => $a['date'] === $wDay['date']);
                    @endphp
                    <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-3 shadow-sm flex flex-col h-full">
                        <div class="text-center pb-2 mb-2 border-b border-gray-100 dark:border-gray-800 {{ $wDay['is_today'] ? 'text-amber-600 font-bold' : '' }}">
                            <span class="text-xs uppercase text-gray-400 block">{{ substr($wDay['day_name'], 0, 3) }}</span>
                            <span class="text-lg font-extrabold">{{ $wDay['day_num'] }}</span>
                        </div>

                        <div class="space-y-2 flex-1 overflow-y-auto max-h-96">
                            @forelse($dayAppts as $appt)
                                <div class="rounded-xl p-2 border text-xs bg-amber-50/60 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800">
                                    <div class="font-bold text-gray-900 dark:text-white truncate">{{ $appt['client_name'] }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $appt['start_time'] }} - {{ $appt['service_name'] }}</div>
                                </div>
                            @empty
                                <div class="text-center text-gray-300 text-[11px] py-4">Aucun RDV</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- VUE MOIS (Month Grid) -->
            <div class="rounded-2xl bg-white dark:bg-gray-900 p-4 border border-gray-200/80 dark:border-gray-800 shadow-sm text-center">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                    Aperçu mensuel : <strong>{{ count($appointments) }} rendez-vous</strong> enregistrés pour le mois de {{ $periodLabel }}.
                </p>
                <div class="mt-3 flex justify-center gap-2">
                    <x-filament::button wire:click="setViewMode('day')" size="sm" color="amber">
                        Basculer en Vue Jour (Agenda)
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
