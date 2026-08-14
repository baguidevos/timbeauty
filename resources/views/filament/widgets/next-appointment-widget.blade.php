<x-filament-widgets::widget>
    @if(!$appointment)
        <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border-2 border-dashed border-amber-300/80 dark:border-amber-800/60 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 shrink-0">
                <x-heroicon-o-calendar-days class="h-6 w-6" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Aucun rendez-vous à venir</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Profitez-en pour préparer les postes de coiffure ou contacter vos clients fidèles.
                </p>
            </div>
            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60">
                Créneau libre
            </span>
        </div>
    @else
        @php
            $bgGradient = $isSoon ? 'bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-l-amber-500' : ($isPast ? 'border-l-gray-400 opacity-80' : 'bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border-l-emerald-500');
            $badgeBg = $isSoon ? 'bg-amber-500 text-white' : ($isPast ? 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300' : 'bg-emerald-500 text-white');
        @endphp

        <div class="rounded-2xl bg-white dark:bg-gray-900 p-5 border border-gray-200/80 dark:border-gray-800 border-l-4 {{ $bgGradient }} shadow-sm hover:shadow-md transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Hero Badge -->
                <div class="flex items-center gap-4 shrink-0">
                    <div class="flex flex-col items-center justify-center rounded-xl px-4 py-2.5 {{ $badgeBg }} shadow-sm min-w-[90px]">
                        <span class="text-[9px] font-bold uppercase tracking-wider opacity-80">
                            {{ $isPast ? 'Passé' : 'Prochain RDV' }}
                        </span>
                        <span class="text-base font-bold tabular-nums leading-tight mt-0.5">
                            {{ $countdownText }}
                        </span>
                        <span class="text-[10px] opacity-80 tabular-nums mt-0.5">
                            {{ \App\Helpers\FormatHelper::formatTime($appointment['start_time']) }}
                        </span>
                    </div>
                </div>

                <!-- Appointment details -->
                <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <x-heroicon-o-user class="h-4 w-4 text-gray-400 shrink-0" />
                        <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                            {{ $appointment['client_name'] }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium border {{ $appointment['status'] === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300' }}">
                            {{ $appointment['status'] === 'confirmed' ? 'Confirmé' : 'En attente' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-scissors class="h-3.5 w-3.5 shrink-0 text-amber-500" />
                        <span class="truncate">{{ $appointment['service_name'] }}</span>
                        <span>·</span>
                        <span>avec {{ $appointment['barber_name'] }}</span>
                    </div>
                </div>

                <!-- Action Button link to Appointments page -->
                <div class="flex items-center self-start sm:self-center">
                    <a href="/admin/appointments" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                        <span>Voir agenda</span>
                        <x-heroicon-m-arrow-right class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
