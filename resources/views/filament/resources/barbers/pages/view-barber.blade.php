<x-filament-panels::page>
    @php
        $stats = $this->getBarberStats();
        $barber = $this->record;
        $appointments = $this->getAppointmentsList();
        $attendances = $this->getAttendancesList();
        $sales = $this->getSalesList();
        $absences = $this->getAbsencesList();
        $payrolls = $this->getPayrollsList();
        $photos = $this->getPhotosList();
    @endphp

    <div class="space-y-6">
        <!-- ─── 1. Header Collaborateur & Statut en Direct ──────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <!-- Glow Effect -->
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl dark:bg-amber-500/15"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Avatar & Identité -->
                <div class="flex items-start gap-4 sm:items-center">
                    <div class="relative flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-600 to-amber-800 text-2xl font-black text-white shadow-lg shadow-amber-500/20 ring-4 ring-amber-500/20">
                        {{ strtoupper(substr($barber->firstName, 0, 1) . substr($barber->lastName, 0, 1)) }}

                        <!-- Live Attendance Status Dot -->
                        @php
                            $statusDotColor = match($stats['todayStatus']) {
                                'present' => 'bg-emerald-500 ring-white dark:ring-gray-900',
                                'break' => 'bg-amber-400 ring-white dark:ring-gray-900',
                                'completed' => 'bg-sky-500 ring-white dark:ring-gray-900',
                                'on_leave' => 'bg-rose-500 ring-white dark:ring-gray-900',
                                default => 'bg-gray-400 ring-white dark:ring-gray-900',
                            };
                        @endphp
                        <div class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full {{ $statusDotColor }} ring-2 shadow-md"></div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white">
                                {{ $barber->getFullName() }}
                            </h1>

                            <!-- Rôle / Job Title -->
                            <x-filament::badge color="primary" size="sm">
                                {{ match($barber->jobTitle) {
                                    'barber' => 'Coiffeur / Barbier',
                                    'manager' => 'Gérant / Manager',
                                    'receptionist' => 'Réceptionniste',
                                    'cashier' => 'Caissier',
                                    default => $barber->jobTitle ?? 'Collaborateur',
                                } }}
                            </x-filament::badge>

                            <!-- Statut de présence aujourd'hui -->
                            @if($stats['todayStatus'] === 'present')
                                <x-filament::badge color="success" icon="heroicon-m-check-circle" size="sm">
                                    Présent • Arrivé à {{ $stats['todayAttendance']->clock_in_time }}
                                </x-filament::badge>
                            @elseif($stats['todayStatus'] === 'break')
                                <x-filament::badge color="warning" icon="heroicon-m-pause-circle" size="sm">
                                    En Pause
                                </x-filament::badge>
                            @elseif($stats['todayStatus'] === 'completed')
                                <x-filament::badge color="info" icon="heroicon-m-arrow-left-start-on-rectangle" size="sm">
                                    Journée terminée ({{ $stats['todayAttendance']->worked_hours_formatted }})
                                </x-filament::badge>
                            @elseif($stats['todayStatus'] === 'on_leave')
                                <x-filament::badge color="danger" icon="heroicon-m-sun" size="sm">
                                    En congé
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="gray" icon="heroicon-m-clock" size="sm">
                                    Non pointé aujourd'hui
                                </x-filament::badge>
                            @endif
                        </div>

                        <!-- Coordonnées & Rémunération -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            @if($barber->phone)
                                <a href="tel:{{ $barber->phone }}" class="flex items-center gap-1.5 transition hover:text-amber-600 dark:hover:text-amber-400">
                                    <x-heroicon-m-phone class="h-4 w-4 text-gray-400" />
                                    <span>{{ $barber->phone }}</span>
                                </a>
                            @endif

                            @if($barber->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $barber->phone);
                                    if(strlen($cleanPhone) === 8) $cleanPhone = '228'.$cleanPhone;
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="flex items-center gap-1.5 font-medium text-emerald-600 transition hover:underline dark:text-emerald-400">
                                    <x-heroicon-m-chat-bubble-left-ellipsis class="h-4 w-4" />
                                    <span>WhatsApp</span>
                                </a>
                            @endif

                            <span class="flex items-center gap-1.5">
                                <x-heroicon-m-banknotes class="h-4 w-4 text-gray-400" />
                                <span>
                                    Contrat : <strong>{{ match($barber->remunerationType) {
                                        'fixed' => 'Salaire fixe (' . \App\Helpers\FormatHelper::formatFCFA($barber->fixedSalary) . ')',
                                        'commission' => 'Commission (' . (float) $barber->commissionRate . '%)',
                                        'fixed_plus_commission' => 'Fixe (' . \App\Helpers\FormatHelper::formatFCFA($barber->fixedSalary) . ') + ' . (float) $barber->commissionRate . '%',
                                        'per_service' => 'Au service (' . \App\Helpers\FormatHelper::formatFCFA($barber->perServiceRate) . '/prestation)',
                                        default => 'Non défini',
                                    } }}</strong>
                                </span>
                            </span>

                            <span class="flex items-center gap-1.5">
                                <x-heroicon-m-calendar class="h-4 w-4 text-gray-400" />
                                <span>Embauché le {{ $stats['hireDateFormatted'] }} ({{ $stats['seniority'] }})</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions Rapides -->
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <x-filament::button
                        tag="a"
                        href="{{ \App\Filament\Resources\StaffSchedules\StaffScheduleResource::getUrl('index') }}"
                        color="info"
                        icon="heroicon-m-calendar-days"
                        size="sm"
                        outlined
                    >
                        Planning
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- ─── 2. Cartes Métriques Clés (4 Hero KPI Cards) ─────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- CA Généré & Commissions -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">CA du Mois</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-gray-950 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['monthRevenue']) }}
                    </span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Commissions est. : <strong class="text-emerald-600 dark:text-emerald-400">{{ \App\Helpers\FormatHelper::formatFCFA($stats['estimatedCommission']) }}</strong>
                    </p>
                </div>
            </div>

            <!-- Volume de Prestations -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Prestations Réalisées</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400">
                        <x-heroicon-o-scissors class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-gray-950 dark:text-white">
                        {{ $stats['monthAppointmentsCount'] }} <span class="text-sm font-normal text-gray-400">ce mois</span>
                    </span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Total carrière : <strong class="text-gray-700 dark:text-gray-300">{{ $stats['totalAppointmentsCount'] }} coupes</strong>
                    </p>
                </div>
            </div>

            <!-- Heures & Ponctualité -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Heures & Ponctualité</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <x-heroicon-o-clock class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-gray-950 dark:text-white">
                        {{ $stats['totalHours'] }}h
                    </span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Taux ponctualité : <strong class="text-emerald-600 dark:text-emerald-400">{{ $stats['punctualityRate'] }}%</strong> ({{ $stats['totalDaysWorked'] }} j. travaillés)
                    </p>
                </div>
            </div>

            <!-- Clientèle Fidèle -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clients Réguliers</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                        <x-heroicon-o-users class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-gray-950 dark:text-white">
                        {{ $stats['regularClientsCount'] }} <span class="text-sm font-normal text-gray-400">habitués</span>
                    </span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Aujourd'hui : <strong class="text-purple-600 dark:text-purple-400">{{ $stats['todayAppointmentsCount'] }} RDV prévus</strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- ─── 3. Compétences & Objectif Mensuel ───────────────────────────────── -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Spécialités & Prestation Top -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <x-heroicon-m-sparkles class="h-4 w-4 text-amber-500" />
                    Spécialités & Prestation Phare
                </h3>

                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-800/60">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 font-bold">
                        <x-heroicon-o-scissors class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[11px] font-semibold uppercase text-gray-400">Prestation la plus réalisée</span>
                        <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                            {{ $stats['topService'] ? $stats['topService']->name : 'En cours d\'analyse' }}
                        </p>
                    </div>
                </div>

                <!-- Spécialités -->
                <div>
                    <span class="text-[11px] font-semibold uppercase text-gray-400 block mb-1.5">Compétences & Spécialités</span>
                    @if($barber->specialties && count((array)$barber->specialties) > 0)
                        <div class="flex flex-wrap gap-1.5">
                            @foreach((array)$barber->specialties as $sp)
                                <x-filament::badge color="gray" size="sm">
                                    {{ $sp }}
                                </x-filament::badge>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400">Toutes prestations de coiffure & barbe.</p>
                    @endif
                </div>
            </div>

            <!-- Objectif de CA du Mois -->
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <x-heroicon-m-arrow-trending-up class="h-4 w-4 text-emerald-500" />
                        Objectif de Chiffre d'Affaires ({{ now()->translatedFormat('F Y') }})
                    </h3>

                    <div class="mt-4 space-y-2">
                        @if($stats['targetAmount'] > 0)
                            <div class="flex justify-between items-baseline text-xs font-semibold">
                                <span class="text-gray-900 dark:text-white text-sm">
                                    {{ \App\Helpers\FormatHelper::formatFCFA($stats['monthRevenue']) }} / {{ \App\Helpers\FormatHelper::formatFCFA($stats['targetAmount']) }}
                                </span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                                    {{ $stats['targetProgress'] }}% réalisé
                                </span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $stats['targetProgress'] }}%"></div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-800 p-4 text-center text-gray-400 text-xs">
                                <p>Aucun objectif financier individuel n'a été défini pour ce mois.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span>Statut : <strong class="text-gray-800 dark:text-gray-200">{{ $barber->status === 'active' ? 'Actif' : ($barber->status === 'on_leave' ? 'En congé' : 'Inactif') }}</strong></span>
                    <span>Peut réaliser des prestations : <strong class="text-gray-800 dark:text-gray-200">{{ $barber->canPerformServices ? 'Oui' : 'Non' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- ─── 4. Onglets Interactifs & Historique 360° ────────────────────────── -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <!-- Navigation des Onglets -->
            <div class="flex border-b border-gray-200 dark:border-gray-800 bg-gray-50/75 dark:bg-gray-950/40 px-4 overflow-x-auto">
                <button
                    wire:click="$set('activeTab', 'appointments')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'appointments' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-calendar-days class="h-4 w-4" />
                    Planning & Rendez-vous ({{ $appointments->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'attendances')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'attendances' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-clock class="h-4 w-4" />
                    Pointages & Présence ({{ $attendances->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'sales')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'sales' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-receipt-percent class="h-4 w-4" />
                    Prestations & Ventes ({{ $sales->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'absences')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'absences' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-sun class="h-4 w-4" />
                    Congés & Absences ({{ $absences->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'payrolls')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'payrolls' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-banknotes class="h-4 w-4" />
                    Fiches de Paie ({{ $payrolls->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'photos')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'photos' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-camera class="h-4 w-4" />
                    Portfolio Réalisations ({{ $photos->count() }})
                </button>
            </div>

            <!-- Contenu de l'onglet -->
            <div class="p-6">
                <!-- Onglet 1 : Rendez-vous -->
                @if($activeTab === 'appointments')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Date & Heure</th>
                                    <th class="pb-3">Client</th>
                                    <th class="pb-3">Prestation</th>
                                    <th class="pb-3">Statut</th>
                                    <th class="pb-3 text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($appointments as $apt)
                                    @php
                                        $statusColor = match($apt->status) {
                                            'completed' => 'success',
                                            'confirmed' => 'info',
                                            'in_progress' => 'warning',
                                            'cancelled', 'no_show' => 'danger',
                                            default => 'gray',
                                        };
                                        $statusLabel = match($apt->status) {
                                            'completed' => 'Terminé',
                                            'confirmed' => 'Confirmé',
                                            'in_progress' => 'En cours',
                                            'pending' => 'En attente',
                                            'cancelled' => 'Annulé',
                                            'no_show' => 'Absent',
                                            default => $apt->status,
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 font-semibold text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($apt->date)->format('d/m/Y') }}
                                            <span class="text-gray-400 font-normal">({{ substr($apt->startTime, 0, 5) }} - {{ substr($apt->endTime, 0, 5) }})</span>
                                        </td>
                                        <td class="py-3 font-medium text-gray-800 dark:text-gray-200">
                                            {{ $apt->client ? $apt->client->firstName . ' ' . $apt->client->lastName : 'Sans RDV' }}
                                        </td>
                                        <td class="py-3 text-gray-700 dark:text-gray-300">
                                            {{ $apt->service?->name ?? '-' }}
                                        </td>
                                        <td class="py-3">
                                            <x-filament::badge :color="$statusColor" size="sm">
                                                {{ $statusLabel }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="py-3 text-right font-bold text-gray-900 dark:text-white tabular-nums">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($apt->service?->price ?? 0) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">
                                            Aucun rendez-vous assigné.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 2 : Pointages -->
                @if($activeTab === 'attendances')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Date</th>
                                    <th class="pb-3">Arrivée</th>
                                    <th class="pb-3">Départ</th>
                                    <th class="pb-3">Heures Travaillées</th>
                                    <th class="pb-3 text-right">Ponctualité</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($attendances as $att)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 font-semibold text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($att->date)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3 font-mono text-gray-700 dark:text-gray-300">
                                            {{ $att->clock_in_time ?? '—' }}
                                        </td>
                                        <td class="py-3 font-mono text-gray-700 dark:text-gray-300">
                                            {{ $att->clock_out_time ?? ($att->clockIn ? 'En cours...' : '—') }}
                                        </td>
                                        <td class="py-3 font-bold text-gray-900 dark:text-white tabular-nums">
                                            {{ $att->worked_hours_formatted }}
                                        </td>
                                        <td class="py-3 text-right">
                                            @if($att->isLate())
                                                <x-filament::badge color="danger" size="sm">
                                                    En retard
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="success" size="sm">
                                                    À l'heure
                                                </x-filament::badge>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">
                                            Aucun pointage enregistré.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 3 : Prestations & Ventes -->
                @if($activeTab === 'sales')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">N° Vente</th>
                                    <th class="pb-3">Date</th>
                                    <th class="pb-3">Client</th>
                                    <th class="pb-3">Articles</th>
                                    <th class="pb-3 text-right">Montant</th>
                                    <th class="pb-3 text-right">Paiement</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($sales as $sale)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 font-bold text-amber-600 dark:text-amber-400">
                                            #{{ $sale->id }}
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300">
                                            {{ $sale->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 text-gray-800 dark:text-gray-200 font-medium">
                                            {{ $sale->client ? $sale->client->firstName . ' ' . $sale->client->lastName : 'Client libre' }}
                                        </td>
                                        <td class="py-3 text-gray-700 dark:text-gray-300">
                                            {{ $sale->items->pluck('name')->implode(', ') ?: 'Prestation' }}
                                        </td>
                                        <td class="py-3 text-right font-black text-gray-900 dark:text-white tabular-nums">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($sale->total) }}
                                        </td>
                                        <td class="py-3 text-right">
                                            <x-filament::badge color="gray" size="sm">
                                                {{ ucfirst($sale->paymentMethod) }}
                                            </x-filament::badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-400">
                                            Aucune vente rattachée à ce collaborateur.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 4 : Absences & Congés -->
                @if($activeTab === 'absences')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Période</th>
                                    <th class="pb-3">Type</th>
                                    <th class="pb-3">Motif</th>
                                    <th class="pb-3 text-right">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($absences as $abs)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 font-semibold text-gray-900 dark:text-white">
                                            Du {{ \Carbon\Carbon::parse($abs->startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($abs->endDate)->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3">
                                            <x-filament::badge color="warning" size="sm">
                                                {{ match($abs->type) {
                                                    'vacation' => 'Congé payé',
                                                    'sick' => 'Maladie',
                                                    'unpaid' => 'Sans solde',
                                                    default => $abs->type,
                                                } }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="py-3 text-gray-700 dark:text-gray-300">
                                            {{ $abs->reason ?? 'Non renseigné' }}
                                        </td>
                                        <td class="py-3 text-right">
                                            <x-filament::badge :color="$abs->status === 'approved' ? 'success' : ($abs->status === 'rejected' ? 'danger' : 'warning')" size="sm">
                                                {{ match($abs->status) {
                                                    'approved' => 'Validé',
                                                    'rejected' => 'Refusé',
                                                    default => 'En attente',
                                                } }}
                                            </x-filament::badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-400">
                                            Aucune demande de congé ou absence enregistrée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 5 : Fiches de Paie -->
                @if($activeTab === 'payrolls')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Période</th>
                                    <th class="pb-3 text-right">Salaire Base</th>
                                    <th class="pb-3 text-right">Commissions</th>
                                    <th class="pb-3 text-right">Net à Payer</th>
                                    <th class="pb-3 text-right">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($payrolls as $pay)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 font-semibold text-gray-900 dark:text-white">
                                            {{ $pay->month }}/{{ $pay->year }}
                                        </td>
                                        <td class="py-3 text-right text-gray-600 dark:text-gray-300 tabular-nums">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($pay->baseSalary) }}
                                        </td>
                                        <td class="py-3 text-right text-emerald-600 dark:text-emerald-400 font-bold tabular-nums">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($pay->commissions) }}
                                        </td>
                                        <td class="py-3 text-right font-black text-gray-900 dark:text-white tabular-nums">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($pay->netSalary) }}
                                        </td>
                                        <td class="py-3 text-right">
                                            <x-filament::badge :color="$pay->status === 'paid' ? 'success' : 'warning'" size="sm">
                                                {{ $pay->status === 'paid' ? 'Payé' : 'En attente' }}
                                            </x-filament::badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">
                                            Aucune fiche de paie générée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 6 : Portfolio Réalisations -->
                @if($activeTab === 'photos')
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        @forelse($photos as $photo)
                            <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? 'Coiffure' }}" class="h-44 w-full object-cover transition duration-300 group-hover:scale-105" />
                                <div class="p-2.5">
                                    <div class="flex items-center justify-between text-[10px] text-gray-400">
                                        <span>{{ $photo->isBefore() ? 'Avant' : 'Après' }}</span>
                                        <span>{{ $photo->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($photo->caption)
                                        <p class="truncate text-xs font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                                            {{ $photo->caption }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-10 text-center text-gray-400">
                                <x-heroicon-o-camera class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p class="text-xs">Aucune réalisation photo pour ce collaborateur.</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
