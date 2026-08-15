<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Month / Year Selector Bar -->
        <x-filament::section compact>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-filament::icon-button
                    wire:click="prevMonth"
                    icon="heroicon-m-chevron-left"
                    color="gray"
                    label="Mois précédent"
                />

                <div class="flex items-center gap-3">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="selectedMonth">
                            @foreach($this->monthNames as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="selectedYear">
                            @foreach(range(2024, 2030) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <x-filament::icon-button
                    wire:click="nextMonth"
                    icon="heroicon-m-chevron-right"
                    color="gray"
                    label="Mois suivant"
                />
            </div>
        </x-filament::section>

        <!-- Summary KPIs Row -->
        @php
            $stats = $this->summaryStats;
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Boutique Targets Count -->
            <x-filament::section compact>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-building-storefront class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Objectifs Boutique</p>
                        <p class="text-lg font-bold text-gray-950 dark:text-white">{{ $stats['shopCount'] }}</p>
                    </div>
                </div>
            </x-filament::section>

            <!-- Staff Targets Count -->
            <x-filament::section compact>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400">
                        <x-heroicon-o-user-group class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Objectifs Personnel</p>
                        <p class="text-lg font-bold text-gray-950 dark:text-white">{{ $stats['barberCount'] }}</p>
                    </div>
                </div>
            </x-filament::section>

            <!-- Total Target Amount -->
            <x-filament::section compact>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Cible</p>
                        <p class="text-lg font-bold text-gray-950 dark:text-white">{{ \App\Helpers\FormatHelper::formatFCFA($stats['totalTargetAmount']) }}</p>
                    </div>
                </div>
            </x-filament::section>

            <!-- Average Progress -->
            <x-filament::section compact>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $stats['avgProgress'] >= 80 ? 'bg-emerald-500/10 text-emerald-600' : ($stats['avgProgress'] >= 50 ? 'bg-amber-500/10 text-amber-600' : 'bg-rose-500/10 text-rose-600') }}">
                        <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Progression moyenne</p>
                        <p class="text-lg font-bold {{ $stats['avgProgress'] >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($stats['avgProgress'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                            {{ round($stats['avgProgress']) }}%
                        </p>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Target Cards Grid -->
        @php
            $targets = $this->targets;
        @endphp

        @if($targets->isEmpty())
            <x-filament::empty-state
                icon="heroicon-o-presentation-chart-line"
                heading="Aucun objectif pour ce mois"
                description="Aucun objectif de chiffre d'affaires n'est défini pour {{ $this->monthNames[$this->selectedMonth] }} {{ $this->selectedYear }}."
            >
                <x-slot name="actions">
                    {{ $this->createTargetAction }}
                </x-slot>
            </x-filament::empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($targets as $target)
                    @php
                        $isShop = $target->type === 'shop';
                        $pct = $target->progress_percentage;
                        $clampedPct = min(max($pct, 0), 100);
                        $color = $target->status_color; // emerald, amber, rose
                        $colorClass = match($color) {
                            'emerald' => 'text-emerald-600 dark:text-emerald-400',
                            'amber' => 'text-amber-600 dark:text-amber-400',
                            default => 'text-rose-600 dark:text-rose-400',
                        };
                        $bgClass = match($color) {
                            'emerald' => 'bg-emerald-500',
                            'amber' => 'bg-amber-500',
                            default => 'bg-rose-500',
                        };
                        $ringStroke = match($color) {
                            'emerald' => '#10b981',
                            'amber' => '#f59e0b',
                            default => '#f43f5e',
                        };
                        $circumference = 2 * M_PI * 26;
                        $offset = $circumference - ($clampedPct / 100) * $circumference;
                    @endphp

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-xs overflow-hidden hover:border-amber-500/40 hover:shadow-md transition-all">
                        <!-- Top Accent Stripe -->
                        <div class="h-1.5 w-full {{ $isShop ? 'bg-amber-500' : 'bg-sky-500' }}"></div>

                        <div class="p-5 space-y-4">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-filament::badge
                                        :color="$isShop ? 'warning' : 'info'"
                                        :icon="$isShop ? 'heroicon-m-building-storefront' : 'heroicon-m-user'"
                                        size="sm"
                                    >
                                        {{ $isShop ? 'Boutique' : 'Coiffeur' }}
                                    </x-filament::badge>

                                    @if(!$isShop && $target->barber)
                                        <span class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $target->barber->firstName }} {{ $target->barber->lastName }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1">
                                    {{ ($this->editTargetAction)(['target' => $target->id]) }}
                                    {{ ($this->deleteTargetAction)(['target' => $target->id]) }}
                                </div>
                            </div>

                            <!-- Target Period & Amount -->
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $this->monthNames[$target->month] }} {{ $target->year }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <x-heroicon-o-banknotes class="h-5 w-5 text-amber-500 shrink-0" />
                                    <span class="text-xl font-extrabold text-gray-950 dark:text-white">
                                        {{ \App\Helpers\FormatHelper::formatFCFA($target->targetAmount) }}
                                    </span>
                                </div>
                            </div>

                            <hr class="border-gray-100 dark:border-gray-800" />

                            <!-- Progress Section -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-500 dark:text-gray-400">Progression</span>
                                    <x-filament::badge
                                    class="p-1"
                                        :color="$color === 'emerald' ? 'success' : ($color === 'amber' ? 'warning' : 'danger')"
                                        size="xs"
                                    >
                                        {{ $target->status_label }} ({{ $pct }}%)
                                    </x-filament::badge>
                                </div>

                                <!-- Progress Graphic (Radial + Linear) -->
                                <div class="flex items-center gap-4">
                                    <!-- Circular Radial Progress (SVG) -->
                                    <div class="relative inline-flex items-center justify-center shrink-0" style="width: 64px; height: 64px;">
                                        <svg width="64" height="64" class="-rotate-90">
                                            <circle
                                                cx="32"
                                                cy="32"
                                                r="26"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="5"
                                                class="text-gray-100 dark:text-gray-800"
                                            />
                                            <circle
                                                cx="32"
                                                cy="32"
                                                r="26"
                                                fill="none"
                                                stroke="{{ $ringStroke }}"
                                                stroke-width="5"
                                                stroke-linecap="round"
                                                stroke-dasharray="{{ $circumference }}"
                                                stroke-dashoffset="{{ $offset }}"
                                                class="transition-all duration-700 ease-out"
                                            />
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-xs font-extrabold {{ $colorClass }}">
                                                {{ round($pct) }}%
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Linear Progress & Breakdown -->
                                    <div class="flex-1 space-y-1.5 min-w-0">
                                        <div class="w-full h-2 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                            <div class="h-full rounded-full {{ $bgClass }} transition-all duration-700" style="width: {{ $clampedPct }}%;"></div>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                            Actuel : <span class="font-semibold text-gray-900 dark:text-gray-100">{{ \App\Helpers\FormatHelper::formatFCFA($target->actual_revenue) }}</span>
                                        </p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                            @if($target->remaining_amount > 0)
                                                Reste : <span class="font-medium text-amber-600 dark:text-amber-400">{{ \App\Helpers\FormatHelper::formatFCFA($target->remaining_amount) }}</span>
                                            @else
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">Objectif atteint ! 🎉</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
