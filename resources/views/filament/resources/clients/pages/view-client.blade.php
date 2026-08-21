<x-filament-panels::page>
    @php
        $stats = $this->getClientStats();
        $client = $this->record;
        $appointments = $this->getAppointmentsList();
        $sales = $this->getSalesList();
        $loyaltyTransactions = $this->getLoyaltyTransactionsList();
        $promotionUsages = $this->getPromotionUsagesList();
        $photos = $this->getPhotosList();
    @endphp

    <div class="space-y-6">
        <!-- ─── 1. Header Fiche Client VIP ──────────────────────────────────────── -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-xs dark:border-gray-800 dark:bg-gray-900">
            <!-- Background Glow Effect -->
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl dark:bg-amber-500/15"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Avatar & Identité -->
                <div class="flex items-start gap-4 sm:items-center">
                    <div class="relative flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-2xl font-black text-white shadow-lg shadow-amber-500/20 ring-4 ring-amber-500/20">
                        {{ strtoupper(substr($client->firstName, 0, 1) . substr($client->lastName, 0, 1)) }}
                        @if($stats['isLoyal'])
                            <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-gray-950 shadow-md ring-2 ring-white dark:ring-gray-900" title="Client Fidèle">
                                <x-heroicon-s-star class="h-3.5 w-3.5" />
                            </div>
                        @endif
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white">
                                {{ $client->getFullName() }}
                            </h1>

                            @if($stats['isBirthday'])
                                <x-filament::badge color="danger" icon="heroicon-m-cake" size="sm">
                                    Anniversaire aujourd'hui 🎉 ({{ $stats['age'] }} ans)
                                </x-filament::badge>
                            @endif

                            @if($stats['currentTier'])
                                <x-filament::badge color="warning" icon="heroicon-m-sparkles" size="sm">
                                    Palier {{ $stats['currentTier']->name }}
                                </x-filament::badge>
                            @endif

                            @if($stats['isLoyal'])
                                <x-filament::badge color="success" icon="heroicon-m-check-badge" size="sm">
                                    Fidèle
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="gray" size="sm">
                                    Client Standard
                                </x-filament::badge>
                            @endif
                        </div>

                        <!-- Coordonnées & Contact rapide -->
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            @if($client->phone)
                                <a href="tel:{{ $client->phone }}" class="flex items-center gap-1.5 transition hover:text-amber-600 dark:hover:text-amber-400">
                                    <x-heroicon-m-phone class="h-4 w-4 text-gray-400" />
                                    <span>{{ $client->phone }}</span>
                                </a>
                            @endif

                            @if($client->whatsapp)
                                @php
                                    $cleanWa = preg_replace('/[^0-9]/', '', $client->whatsapp);
                                    if(strlen($cleanWa) === 8) $cleanWa = '228'.$cleanWa;
                                @endphp
                                <a href="https://wa.me/{{ $cleanWa }}" target="_blank" class="flex items-center gap-1.5 font-medium text-emerald-600 transition hover:underline dark:text-emerald-400">
                                    <x-heroicon-m-chat-bubble-left-ellipsis class="h-4 w-4" />
                                    <span>WhatsApp</span>
                                </a>
                            @endif

                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="flex items-center gap-1.5 transition hover:text-amber-600 dark:hover:text-amber-400">
                                    <x-heroicon-m-envelope class="h-4 w-4 text-gray-400" />
                                    <span>{{ $client->email }}</span>
                                </a>
                            @endif

                            @if($client->address)
                                <span class="flex items-center gap-1.5">
                                    <x-heroicon-m-map-pin class="h-4 w-4 text-gray-400" />
                                    <span>{{ $client->address }}</span>
                                </span>
                            @endif

                            <span class="flex items-center gap-1.5 text-gray-400">
                                <x-heroicon-m-calendar class="h-4 w-4" />
                                <span>Client depuis le {{ $stats['firstVisit'] }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions Rapides -->
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 lg:border-t-0 lg:pt-0">
                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.admin.pages.pos', ['client' => $client->id]) }}"
                        color="warning"
                        icon="heroicon-m-shopping-cart"
                        size="sm"
                    >
                        Encaisser au POS
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.admin.resources.appointments.index') }}"
                        color="info"
                        icon="heroicon-m-calendar-days"
                        size="sm"
                        outlined
                    >
                        Prendre RDV
                    </x-filament::button>
                </div>
            </div>
        </div>

        <!-- ─── 2. Cartes Métriques Clés (4 Hero KPI Cards) ─────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Dépensé (LTV) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Dépensé</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-gray-950 dark:text-white">
                        {{ \App\Helpers\FormatHelper::formatFCFA($stats['totalSpent']) }}
                    </span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Panier moyen : <strong class="text-gray-700 dark:text-gray-300">{{ \App\Helpers\FormatHelper::formatFCFA($stats['avgBasket']) }}</strong>
                    </p>
                </div>
            </div>

            <!-- Visites & Fréquence -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Visites & Fréquence</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400">
                        <x-heroicon-o-scissors class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-black text-gray-950 dark:text-white">
                        {{ $stats['totalVisits'] }} {{ $stats['totalVisits'] > 1 ? 'visites' : 'visite' }}
                    </span>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Dernier passage : <strong class="text-gray-700 dark:text-gray-300">{{ $stats['lastVisitHuman'] }}</strong>
                    </p>
                </div>
            </div>

            <!-- Points de Fidélité & Progression -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Solde Fidélité</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <x-heroicon-o-star class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                            {{ number_format($stats['loyaltyPoints'], 0, ',', ' ') }}
                        </span>
                        <span class="text-xs font-bold text-gray-500">points</span>
                    </div>

                    @if($stats['nextTier'])
                        <div class="mt-2 space-y-1">
                            <div class="flex justify-between text-[11px] font-medium text-gray-500">
                                <span>Vers {{ $stats['nextTier']->name }}</span>
                                <span>{{ $stats['pointsNeeded'] }} pts restants</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $stats['tierProgress'] }}%"></div>
                            </div>
                        </div>
                    @else
                        <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                            🏆 Palier maximal atteint !
                        </p>
                    @endif
                </div>
            </div>

            <!-- Assiduité & Prochain RDV -->
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rendez-vous & Statut</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                        <x-heroicon-o-calendar-days class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    @if($stats['upcomingAppointment'])
                        <div class="space-y-0.5">
                            <span class="text-sm font-bold text-purple-600 dark:text-purple-400 flex items-center gap-1">
                                <x-heroicon-m-clock class="h-4 w-4" />
                                {{ \Carbon\Carbon::parse($stats['upcomingAppointment']->date)->format('d/m/Y') }} à {{ substr($stats['upcomingAppointment']->startTime, 0, 5) }}
                            </span>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ $stats['upcomingAppointment']->service?->name ?? 'Prestation' }} • {{ $stats['upcomingAppointment']->barber?->firstName ?? 'Coiffeur' }}
                            </p>
                        </div>
                    @else
                        <span class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $stats['attendanceRate'] }}%
                        </span>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Taux de présence ({{ $stats['completedAppointments'] }} honorés sur {{ $stats['totalAppointments'] }})
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- ─── 3. Préférences & Habitudes de Coiffure (Smart Insights) ─────────── -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Coiffeur & Prestation Favoris -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <x-heroicon-m-sparkles class="h-4 w-4 text-amber-500" />
                    Habitudes & Préférences
                </h3>

                <!-- Coiffeur Favori -->
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-800/60">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 font-bold">
                        @if($stats['favoriteBarber'])
                            {{ strtoupper(substr($stats['favoriteBarber']->firstName, 0, 1) . substr($stats['favoriteBarber']->lastName, 0, 1)) }}
                        @else
                            <x-heroicon-o-user class="h-5 w-5 text-gray-400" />
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[11px] font-semibold uppercase text-gray-400">Coiffeur Attitré</span>
                        <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                            {{ $stats['favoriteBarber'] ? $stats['favoriteBarber']->firstName . ' ' . $stats['favoriteBarber']->lastName : 'Non défini' }}
                        </p>
                    </div>
                </div>

                <!-- Prestation Favorite -->
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-800/60">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400">
                        <x-heroicon-o-scissors class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[11px] font-semibold uppercase text-gray-400">Prestation Préférée</span>
                        <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                            {{ $stats['favoriteService'] ? $stats['favoriteService']->name : 'Non définie' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notes & Instructions Particulières (Édition Rapide) -->
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <x-heroicon-m-document-text class="h-4 w-4 text-amber-500" />
                            Notes & Spécificités du Client
                        </h3>
                        @if(!$isEditingNote)
                            <button
                                wire:click="$set('isEditingNote', true)"
                                type="button"
                                class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 flex items-center gap-1"
                            >
                                <x-heroicon-m-pencil-square class="h-3.5 w-3.5" />
                                Modifier
                            </button>
                        @endif
                    </div>

                    <div class="mt-3">
                        @if($isEditingNote)
                            <div class="space-y-3">
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        wire:model="clientNote"
                                        type="textarea"
                                        rows="3"
                                        placeholder="Ex: Cuir chevelu sensible, dégradé à blanc haut, thé vert sans sucre..."
                                    />
                                </x-filament::input.wrapper>
                                <div class="flex justify-end gap-2">
                                    <x-filament::button wire:click="$set('isEditingNote', false)" color="gray" size="xs">
                                        Annuler
                                    </x-filament::button>
                                    <x-filament::button wire:click="saveNotes" color="warning" size="xs">
                                        Enregistrer
                                    </x-filament::button>
                                </div>
                            </div>
                        @else
                            @if($client->notes)
                                <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-line bg-amber-50/50 dark:bg-amber-950/20 p-3.5 rounded-xl border border-amber-200/50 dark:border-amber-900/30">
                                    {{ $client->notes }}
                                </p>
                            @else
                                <div class="flex flex-col items-center justify-center py-6 text-center text-gray-400 border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                                    <x-heroicon-o-chat-bubble-bottom-center-text class="h-6 w-6 mb-1" />
                                    <p class="text-xs">Aucune note ou préférence particulière renseignée.</p>
                                    <button wire:click="$set('isEditingNote', true)" type="button" class="mt-2 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                        + Ajouter des préférences
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Info Naissance & Genre -->
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span>Genre : <strong class="text-gray-800 dark:text-gray-200">{{ $client->gender === 'female' ? 'Femme' : ($client->gender === 'male' ? 'Homme' : 'Autre') }}</strong></span>
                    @if($client->birthDate)
                        <span>Date de naissance : <strong class="text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($client->birthDate)->format('d/m/Y') }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <!-- ─── 4. Onglets Interactifs & Historique 360° ────────────────────────── -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <!-- Navigation des Onglets -->
            <div class="flex border-b border-gray-200 dark:border-gray-800 bg-gray-50/75 dark:bg-gray-950/40 px-4 overflow-x-auto">
                <button
                    wire:click="$set('activeTab', 'overview')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'overview' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-calendar-days class="h-4 w-4" />
                    Rendez-vous ({{ $appointments->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'sales')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'sales' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-receipt-percent class="h-4 w-4" />
                    Factures & Ventes ({{ $sales->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'loyalty')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'loyalty' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-star class="h-4 w-4" />
                    Points Fidélité ({{ $loyaltyTransactions->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'promotions')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'promotions' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-tag class="h-4 w-4" />
                    Promotions Utilisées ({{ $promotionUsages->count() }})
                </button>

                <button
                    wire:click="$set('activeTab', 'photos')"
                    type="button"
                    class="flex items-center gap-2 border-b-2 py-3.5 px-3 text-xs font-bold transition whitespace-nowrap {{ $activeTab === 'photos' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                >
                    <x-heroicon-m-camera class="h-4 w-4" />
                    Galerie Photos ({{ $photos->count() }})
                </button>
            </div>

            <!-- Contenu de l'onglet actif -->
            <div class="p-6">
                <!-- Onglet 1 : Rendez-vous -->
                @if($activeTab === 'overview')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Date & Heure</th>
                                    <th class="pb-3">Prestation</th>
                                    <th class="pb-3">Coiffeur</th>
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
                                        <td class="py-3">
                                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $apt->service?->name ?? '-' }}</span>
                                            @if($apt->service?->duration)
                                                <span class="text-[10px] text-gray-400">({{ $apt->service->duration }} min)</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300">
                                            {{ $apt->barber ? $apt->barber->firstName . ' ' . $apt->barber->lastName : '-' }}
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
                                            Aucun rendez-vous enregistré pour ce client.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 2 : Factures & Ventes -->
                @if($activeTab === 'sales')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">N° Ticket</th>
                                    <th class="pb-3">Date</th>
                                    <th class="pb-3">Articles</th>
                                    <th class="pb-3 text-right">Sous-total</th>
                                    <th class="pb-3 text-right">Remise</th>
                                    <th class="pb-3 text-right">Total Net</th>
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
                                        <td class="py-3 text-gray-700 dark:text-gray-300">
                                            {{ $sale->items->pluck('name')->implode(', ') ?: 'Vente diverse' }}
                                        </td>
                                        <td class="py-3 text-right text-gray-500 tabular-nums">
                                            {{ \App\Helpers\FormatHelper::formatFCFA($sale->subtotal) }}
                                        </td>
                                        <td class="py-3 text-right tabular-nums">
                                            @if($sale->discountAmount > 0)
                                                <span class="font-bold text-rose-600 dark:text-rose-400">- {{ \App\Helpers\FormatHelper::formatFCFA($sale->discountAmount) }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
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
                                        <td colspan="7" class="py-8 text-center text-gray-400">
                                            Aucune vente ou encaissement enregistré.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 3 : Points Fidélité -->
                @if($activeTab === 'loyalty')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Date</th>
                                    <th class="pb-3">Type</th>
                                    <th class="pb-3">Description</th>
                                    <th class="pb-3 text-right">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($loyaltyTransactions as $tx)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 text-gray-600 dark:text-gray-300">
                                            {{ $tx->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3">
                                            <x-filament::badge :color="$tx->type === 'earn' ? 'success' : 'danger'" size="sm">
                                                {{ $tx->type === 'earn' ? 'Gain (+)' : 'Utilisation (-)' }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="py-3 text-gray-800 dark:text-gray-200">
                                            {{ $tx->description ?: ($tx->sale_id ? 'Vente #' . $tx->sale_id : 'Opération fidélité') }}
                                        </td>
                                        <td class="py-3 text-right font-black tabular-nums {{ $tx->type === 'earn' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $tx->type === 'earn' ? '+' : '-' }}{{ number_format($tx->points, 0, ',', ' ') }} pts
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-400">
                                            Aucune transaction de points enregistrée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 4 : Promotions Utilisées -->
                @if($activeTab === 'promotions')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-400 uppercase tracking-wider font-semibold">
                                    <th class="pb-3">Date</th>
                                    <th class="pb-3">Promotion</th>
                                    <th class="pb-3">Type & Valeur</th>
                                    <th class="pb-3 text-right">Vente Associée</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($promotionUsages as $usage)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                                        <td class="py-3 text-gray-600 dark:text-gray-300">
                                            {{ $usage->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 font-bold text-amber-600 dark:text-amber-400">
                                            🏷️ {{ $usage->promotion?->name ?? 'Promotion' }}
                                        </td>
                                        <td class="py-3 text-gray-700 dark:text-gray-300">
                                            {{ $usage->promotion?->formatted_value ?? '-' }}
                                        </td>
                                        <td class="py-3 text-right font-semibold text-gray-900 dark:text-white">
                                            @if($usage->saleId)
                                                Vente #{{ $usage->saleId }} ({{ \App\Helpers\FormatHelper::formatFCFA($usage->sale?->total ?? 0) }})
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-400">
                                            Ce client n'a pas encore utilisé de code promo ou promotion.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Onglet 5 : Galerie Photos -->
                @if($activeTab === 'photos')
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        @forelse($photos as $photo)
                            <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? 'Coupe' }}" class="h-44 w-full object-cover transition duration-300 group-hover:scale-105" />
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
                                <p class="text-xs">Aucune photo de réalisation enregistrée pour ce client.</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
