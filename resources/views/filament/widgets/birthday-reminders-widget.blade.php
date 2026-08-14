<x-filament-widgets::widget>
    @if(count($birthdays) > 0)
        <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 border-l-rose-400 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎂</span>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Anniversaires à venir</h3>
                    @if($thisMonthCount > 0)
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                            {{ $thisMonthCount }} ce mois
                        </span>
                    @endif
                </div>
                <x-filament::button
                    wire:click="generateReminders"
                    size="xs"
                    color="rose"
                    icon="heroicon-o-gift"
                >
                    Générer rappels
                </x-filament::button>
            </div>

            <div class="space-y-2.5">
                @foreach($birthdays as $client)
                    <div class="flex items-center gap-3 rounded-xl p-2.5 {{ $client['is_today'] ? 'bg-rose-50/80 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800' : 'bg-gray-50/60 dark:bg-gray-800/40' }}">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full text-base font-bold shrink-0 {{ $client['is_today'] ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/60 dark:text-rose-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            {{ $client['is_today'] ? '🎂' : strtoupper(substr($client['first_name'], 0, 1) . substr($client['last_name'], 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $client['first_name'] }} {{ $client['last_name'] }}
                                </span>
                                @if($client['is_today'])
                                    <span class="inline-flex items-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white animate-pulse">
                                        Aujourd'hui !
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400 mt-0.5">
                                <span>{{ $client['age'] }} ans</span>
                                @if($client['phone'])
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-phone class="h-3 w-3" /> {{ $client['phone'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            @if(!$client['is_today'])
                                <span class="text-sm font-bold tabular-nums {{ $client['days_until'] <= 3 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ $client['days_until'] }}j
                                </span>
                                <span class="text-[10px] text-gray-400 block">
                                    {{ $client['next_birthday_date'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
