<x-filament-widgets::widget>
    @php
        $daysFr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
        $monthsFr = ['January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'];
        $carbon = \Carbon\Carbon::parse($currentDate);
        $dateFormatted = ($daysFr[$carbon->format('l')] ?? $carbon->format('l')) . ' ' . $carbon->format('j') . ' ' . ($monthsFr[$carbon->format('F')] ?? $carbon->format('F')) . ' ' . $carbon->format('Y');
    @endphp

    <div class="space-y-4">
        <!-- 1. Header & Summary KPIs -->
        <x-filament::section compact>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-finger-print class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-950 dark:text-white">Pointage & Présence du Jour</h3>
                            <x-filament::badge color="warning" size="xs">
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
                    <x-filament::badge color="{{ $attendanceRate >= 80 ? 'success' : ($attendanceRate >= 50 ? 'warning' : 'danger') }}" icon="heroicon-m-chart-bar" size="sm">
                        Taux de présence : {{ $attendanceRate }}%
                    </x-filament::badge>
                    <x-filament::badge color="success" icon="heroicon-m-check-circle" size="sm">
                        Présents : {{ $presentCount }}
                    </x-filament::badge>
                    <x-filament::badge color="warning" icon="heroicon-m-clock" size="sm">
                        En retard : {{ $lateCount }}
                    </x-filament::badge>
                    <x-filament::badge color="danger" icon="heroicon-m-user-minus" size="sm">
                        Absents : {{ $absentCount }}
                    </x-filament::badge>
                    <x-filament::badge color="gray" icon="heroicon-m-users" size="sm">
                        Total effectif : {{ $totalStaff }}
                    </x-filament::badge>
                </div>
            </div>
        </x-filament::section>

        <!-- 2. Staff Attendance Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
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
                        default => 'Non pointé',
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

                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xs hover:border-amber-500/30 hover:shadow-md transition-all flex flex-col justify-between">
                    <!-- Top Info: Avatar, Name, Job, Status -->
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white font-bold text-xs shadow-xs">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-gray-950 dark:text-white truncate">
                                        {{ $staff['barber_name'] }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                        {{ $staff['job_title'] }}
                                    </p>
                                </div>
                            </div>

                            <x-filament::badge :color="$statusColor" :icon="$statusIcon" size="xs">
                                {{ $statusLabel }}
                            </x-filament::badge>
                        </div>

                        <!-- Time & Worked metrics -->
                        <div class="grid grid-cols-3 gap-2 mt-4 p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-semibold block">Arrivée</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-gray-100">
                                    {{ $staff['clock_in'] ?? '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-semibold block">Départ</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-gray-100">
                                    {{ $staff['clock_out'] ?? '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-semibold block">Travaillé</span>
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                                    {{ $staff['worked_hours'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
                        @if($staff['is_clocked_in'])
                            <!-- Clocked in, ready to clock out -->
                            <x-filament::button
                                wire:click="clockOut({{ $staff['attendance_id'] }})"
                                size="xs"
                                color="danger"
                                icon="heroicon-m-arrow-left-on-rectangle"
                                class="w-full"
                            >
                                Pointer le départ
                            </x-filament::button>
                        @elseif($staff['clock_in'] && $staff['clock_out'])
                            <!-- Completed shift -->
                            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                <x-heroicon-m-check class="h-3.5 w-3.5" /> Journée pointée ({{ $staff['worked_hours'] }})
                            </span>
                            <x-filament::button
                                wire:click="clockIn({{ $staff['barber_id'] }})"
                                size="xs"
                                color="gray"
                                icon="heroicon-m-arrow-path"
                            >
                                Re-pointer
                            </x-filament::button>
                        @elseif($staff['status'] === 'absent')
                            <!-- Marked absent -->
                            <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1">
                                <x-heroicon-m-x-mark class="h-3.5 w-3.5" /> Notifié absent
                            </span>
                            <x-filament::button
                                wire:click="clockIn({{ $staff['barber_id'] }})"
                                size="xs"
                                color="success"
                                icon="heroicon-m-arrow-right-on-rectangle"
                            >
                                Pointer arrivée
                            </x-filament::button>
                        @else
                            <!-- Not clocked in yet -->
                            <x-filament::button
                                wire:click="clockIn({{ $staff['barber_id'] }})"
                                size="xs"
                                color="success"
                                icon="heroicon-m-arrow-right-on-rectangle"
                                class="flex-1"
                            >
                                Pointer arrivée
                            </x-filament::button>
                            <x-filament::button
                                wire:click="markAbsent({{ $staff['barber_id'] }})"
                                size="xs"
                                color="gray"
                                icon="heroicon-m-user-minus"
                            >
                                Absent
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
