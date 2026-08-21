<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $matrix = $this->scheduleMatrix;
            $daysOrder = [
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
                0 => 'Dimanche',
            ];
        @endphp

        <!-- 1. En-tête & Filtre -->
        <x-filament::section compact>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-calendar class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-950 dark:text-white">Planning & Horaires Hebdomadaires</h3>
                            <x-filament::badge class="p-1" color="warning" size="xs">
                                {{ count($matrix) }} employés
                            </x-filament::badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Matrice hebdomadaire des horaires de travail et jours de repos de l'équipe
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="selectedBarberId" class="h-8 text-xs">
                            <option value="all">Tous les employés</option>
                            @foreach($this->barbersList as $b)
                                <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    {{ ($this->configureWeekAction) }}
                </div>
            </div>
        </x-filament::section>

        <!-- 2. Matrice Hebdomadaire (Weekly Planning Matrix) -->
        <x-filament::section>
            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50/75 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5 min-w-[200px]">Employé</th>
                            @foreach($daysOrder as $dayNum => $dayName)
                                <th class="px-3 py-3.5 text-center min-w-[110px] {{ in_array($dayNum, [6, 0]) ? 'bg-amber-50/30 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400' : '' }}">
                                    {{ $dayName }}
                                </th>
                            @endforeach
                            <th class="px-6 py-3.5 text-right min-w-[120px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @forelse($matrix as $row)
                            <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/10 transition-colors">
                                <!-- Employé -->
                                <td class="px-6 py-3.5 font-semibold text-gray-950 dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                                            {{ substr($row['name'], 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-gray-950 dark:text-white">{{ $row['name'] }}</p>
                                                @if(($row['status'] ?? '') === 'on_leave')
                                                    <x-filament::badge color="warning" size="xs">
                                                        En congé
                                                    </x-filament::badge>
                                                @elseif(($row['status'] ?? '') === 'inactive')
                                                    <x-filament::badge color="danger" size="xs">
                                                        Inactif
                                                    </x-filament::badge>
                                                @endif
                                            </div>
                                            <p class="text-[10px] text-gray-400 font-normal">{{ $row['job_title'] }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- 7 Days -->
                                @foreach($daysOrder as $dayNum => $dayName)
                                    @php
                                        $item = $row['days'][$dayNum] ?? null;
                                        $isDayOff = $item ? $item['is_day_off'] : false;
                                        $timeStr = $item && !$isDayOff && $item['start_time'] ? "{$item['start_time']} - {$item['end_time']}" : null;
                                    @endphp
                                    <td class="px-3 py-3 text-center {{ in_array($dayNum, [6, 0]) ? 'bg-amber-50/15 dark:bg-amber-950/10' : '' }}">
                                        @if($isDayOff)
                                            <button
                                                type="button"
                                                wire:click="toggleDayOff({{ $row['id'] }}, {{ $dayNum }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60 hover:scale-105 transition-all"
                                                title="Cliquer pour activer ce jour"
                                            >
                                                <x-heroicon-m-moon class="h-3 w-3" /> Repos
                                            </button>
                                        @elseif($timeStr)
                                            <button
                                                type="button"
                                                wire:click="toggleDayOff({{ $row['id'] }}, {{ $dayNum }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60 hover:scale-105 transition-all tabular-nums"
                                                title="Cliquer pour basculer en repos"
                                            >
                                                <x-heroicon-m-clock class="h-3 w-3 text-emerald-500" /> {{ $timeStr }}
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="quickSetHours({{ $row['id'] }}, {{ $dayNum }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium text-gray-400 hover:text-amber-600 border border-dashed border-gray-200 dark:border-gray-700 hover:border-amber-400 transition-all"
                                                title="Définir les horaires standards"
                                            >
                                                + Définir
                                            </button>
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Actions -->
                                <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                    <x-filament::button
                                        wire:click="editBarberWeek({{ $row['id'] }})"
                                        size="xs"
                                        color="gray"
                                        icon="heroicon-o-pencil-square"
                                    >
                                        Modifier
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                                    Aucun employé trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
