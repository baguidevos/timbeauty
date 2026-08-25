<x-filament-panels::page>
    @php
        $systemInfo = $this->systemInfo;
        $users = $this->users;
        $activityLogs = $this->activityLogs;
    @endphp

    <div class="space-y-6" x-data="{ activeTab: @entangle('activeTab') }">

        <!-- 1. En-tête & Cartes Statistiques Système -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xs flex items-center gap-3 transition-all hover:border-amber-500/30 hover:shadow-md">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <x-heroicon-o-information-circle class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Version Système</p>
                    <p class="text-base font-bold text-gray-950 dark:text-white truncate">v{{ $systemInfo['version'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xs flex items-center gap-3 transition-all hover:border-amber-500/30 hover:shadow-md">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <x-heroicon-o-circle-stack class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Base de données</p>
                    <p class="text-base font-bold text-gray-950 dark:text-white truncate">{{ $systemInfo['database'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xs flex items-center gap-3 transition-all hover:border-amber-500/30 hover:shadow-md">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <x-heroicon-o-banknotes class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Devise Principale</p>
                    <p class="text-base font-bold text-gray-950 dark:text-white truncate">{{ $systemInfo['currency'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-xs flex items-center gap-3 transition-all hover:border-amber-500/30 hover:shadow-md">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <x-heroicon-o-users class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Comptes Utilisateurs</p>
                    <p class="text-base font-bold text-gray-950 dark:text-white truncate">{{ $systemInfo['users_count'] }} ({{ $systemInfo['active_users_count'] }} actifs)</p>
                </div>
            </div>
        </div>

        <!-- 2. Barre d'onglets de navigation -->
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-1.5 shadow-xs overflow-x-auto">
            <nav class="flex space-x-1 sm:space-x-2 min-w-max" aria-label="Tabs">
                <button
                    type="button"
                    @click="activeTab = 'shop'"
                    :class="activeTab === 'shop' 
                        ? 'bg-amber-500 text-white shadow-xs font-semibold' 
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/60 font-medium'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150"
                >
                    <x-heroicon-o-building-storefront class="h-4 w-4 shrink-0" />
                    <span>Salon</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'cash'"
                    :class="activeTab === 'cash' 
                        ? 'bg-amber-500 text-white shadow-xs font-semibold' 
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/60 font-medium'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150"
                >
                    <x-heroicon-o-currency-dollar class="h-4 w-4 shrink-0" />
                    <span>Caisse & Règlements</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'loyalty'"
                    :class="activeTab === 'loyalty' 
                        ? 'bg-amber-500 text-white shadow-xs font-semibold' 
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/60 font-medium'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150"
                >
                    <x-heroicon-o-heart class="h-4 w-4 shrink-0" />
                    <span>Fidélité</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'tickets'"
                    :class="activeTab === 'tickets' 
                        ? 'bg-amber-500 text-white shadow-xs font-semibold' 
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/60 font-medium'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150"
                >
                    <x-heroicon-o-receipt-percent class="h-4 w-4 shrink-0" />
                    <span>Tickets & Impression</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'users'"
                    :class="activeTab === 'users' 
                        ? 'bg-amber-500 text-white shadow-xs font-semibold' 
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/60 font-medium'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150"
                >
                    <x-heroicon-o-user-group class="h-4 w-4 shrink-0" />
                    <span>Utilisateurs</span>
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-700 dark:text-amber-300" :class="activeTab === 'users' ? 'bg-white/30 text-white' : ''">
                        {{ $systemInfo['users_count'] }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'activity'"
                    :class="activeTab === 'activity' 
                        ? 'bg-amber-500 text-white shadow-xs font-semibold' 
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800/60 font-medium'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150"
                >
                    <x-heroicon-o-clock class="h-4 w-4 shrink-0" />
                    <span>Journal d'activité</span>
                </button>
            </nav>
        </div>

        <!-- ========================================================================= -->
        <!-- ONGLET 1 : INFORMATIONS DU SALON -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'shop'" x-cloak class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-building-storefront class="h-5 w-5 text-amber-500" />
                        <span>Informations générales du salon</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Coordonnées officielles, nom commercial et coordonnées apparaissant sur les factures et tickets.
                </x-slot>

                <form wire:submit.prevent="saveShop" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Nom du salon *
                            </label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="text"
                                    wire:model="shop_name"
                                    placeholder="Ex: BarberShop Pro Lomé"
                                    required
                                />
                            </x-filament::input.wrapper>
                            @error('shop_name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Email de contact
                            </label>
                            <x-filament::input.wrapper prefix-icon="heroicon-m-envelope">
                                <x-filament::input
                                    type="email"
                                    wire:model="shop_email"
                                    placeholder="contact@salon.com"
                                />
                            </x-filament::input.wrapper>
                            @error('shop_email') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Téléphone
                            </label>
                            <x-filament::input.wrapper prefix-icon="heroicon-m-phone">
                                <x-filament::input
                                    type="text"
                                    wire:model="shop_phone"
                                    placeholder="+228 90 00 00 00"
                                />
                            </x-filament::input.wrapper>
                            @error('shop_phone') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Devise monétaire *
                            </label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="text"
                                    wire:model="currency"
                                    placeholder="FCFA"
                                    required
                                />
                            </x-filament::input.wrapper>
                            @error('currency') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Adresse physique
                            </label>
                            <x-filament::input.wrapper prefix-icon="heroicon-m-map-pin">
                                <x-filament::input
                                    type="text"
                                    wire:model="shop_address"
                                    placeholder="Adresse complète du salon..."
                                />
                            </x-filament::input.wrapper>
                            @error('shop_address') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Horaires d'ouverture -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1 flex items-center gap-1.5">
                            <x-heroicon-o-clock class="h-4 w-4 text-amber-500" />
                            Horaires d'ouverture standards
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            Plage horaire par défaut utilisée pour la prise de rendez-vous et la planification.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Heure d'ouverture
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="time"
                                        wire:model.live="default_opening_time"
                                        required
                                    />
                                </x-filament::input.wrapper>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Heure de fermeture
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="time"
                                        wire:model.live="default_closing_time"
                                        required
                                    />
                                </x-filament::input.wrapper>
                            </div>
                        </div>

                        <div class="mt-3 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between text-xs text-amber-800 dark:text-amber-300">
                            <span class="font-medium">Créneau d'activité configuré :</span>
                            <span class="font-bold tabular-nums">{{ $default_opening_time }} — {{ $default_closing_time }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                            Sauvegarder les informations
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- ========================================================================= -->
        <!-- ONGLET 2 : CAISSE & RÈGLEMENTS -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'cash'" x-cloak class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-currency-dollar class="h-5 w-5 text-amber-500" />
                        <span>Mode de gestion de caisse</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Configurez comment les transactions et sessions de caisse sont encadrées dans le salon.
                </x-slot>

                <form wire:submit.prevent="saveCash" class="space-y-6">
                    <!-- Mode de fonctionnement -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $cash_register_mode === 'auto_open' ? 'border-amber-500 bg-amber-500/5' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <x-heroicon-o-bolt class="h-4 w-4 text-amber-500" />
                                    Ouverture Auto
                                </span>
                                <input type="radio" name="cash_mode" value="auto_open" wire:model.live="cash_register_mode" class="text-amber-600 focus:ring-amber-500" />
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Ouvre automatiquement une session lors de la première vente du jour en reprenant le dernier solde.
                            </p>
                        </label>

                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $cash_register_mode === 'strict' ? 'border-amber-500 bg-amber-500/5' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <x-heroicon-o-lock-closed class="h-4 w-4 text-amber-500" />
                                    Mode Strict
                                </span>
                                <input type="radio" name="cash_mode" value="strict" wire:model.live="cash_register_mode" class="text-amber-600 focus:ring-amber-500" />
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Bloque tout encaissement en espèces tant qu'un caissier n'a pas explicitement ouvert la caisse.
                            </p>
                        </label>

                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all {{ $cash_register_mode === 'flexible' ? 'border-amber-500 bg-amber-500/5' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <x-heroicon-o-arrow-path-rounded-square class="h-4 w-4 text-amber-500" />
                                    Mode Flexible
                                </span>
                                <input type="radio" name="cash_mode" value="flexible" wire:model.live="cash_register_mode" class="text-amber-600 focus:ring-amber-500" />
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Permet les ventes même sans caisse physique ouverte, rattachées rétroactivement.
                            </p>
                        </label>
                    </div>

                    <!-- Moyens de paiement activés -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Moyens de paiement acceptés</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Sélectionnez les modes de règlement qui apparaîtront lors des encaissements au comptoir.
                            </p>
                        </div>

                        @php
                            $paymentMethodsList = [
                                ['id' => 'cash', 'label' => 'Espèces (Cash)', 'icon' => 'heroicon-o-banknotes', 'color' => 'text-emerald-500', 'desc' => 'Billets et pièces en caisse'],
                                ['id' => 'tmoney', 'label' => 'TMoney (Togocom)', 'icon' => 'heroicon-o-device-phone-mobile', 'color' => 'text-amber-500', 'desc' => 'Paiement mobile money TMoney'],
                                ['id' => 'flooz', 'label' => 'Flooz (Moov Africa)', 'icon' => 'heroicon-o-device-phone-mobile', 'color' => 'text-blue-500', 'desc' => 'Paiement mobile money Flooz'],
                                ['id' => 'card', 'label' => 'Carte Bancaire / TPE', 'icon' => 'heroicon-o-credit-card', 'color' => 'text-purple-500', 'desc' => 'Terminal de paiement par carte'],
                                ['id' => 'transfer', 'label' => 'Virement bancaire', 'icon' => 'heroicon-o-arrows-right-left', 'color' => 'text-sky-500', 'desc' => 'Virement de compte à compte'],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($paymentMethodsList as $method)
                                @php
                                    $isEnabled = in_array($method['id'], $enabled_payment_methods, true);
                                @endphp
                                <div 
                                    wire:click="togglePaymentMethod('{{ $method['id'] }}')"
                                    class="p-3.5 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between gap-3 {{ $isEnabled ? 'border-amber-500/60 bg-amber-500/5' : 'border-gray-200 dark:border-gray-800 opacity-60 hover:opacity-100' }}"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <x-dynamic-component :component="$method['icon']" class="h-5 w-5 {{ $method['color'] }} shrink-0" />
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $method['label'] }}</p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $method['desc'] }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 flex h-5 w-5 items-center justify-center rounded-full text-xs {{ $isEnabled ? 'bg-amber-500 text-white font-bold' : 'bg-gray-200 dark:bg-gray-800 text-gray-400' }}">
                                        @if($isEnabled)
                                            ✓
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Paramètres Bancaires & Seuil d'écrémage -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <x-heroicon-o-building-library class="h-4 w-4 text-amber-500" />
                                Écrémage de caisse & Compte bancaire du salon
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Définissez le seuil de sécurité en caisse à partir duquel une alerte invite à effectuer un versement en banque.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Seuil d'alerte de versement (FCFA) *
                                </label>
                                <x-filament::input.wrapper prefix-icon="heroicon-m-banknotes">
                                    <x-filament::input
                                        type="number"
                                        wire:model="cash_bank_deposit_threshold"
                                        placeholder="150000"
                                        required
                                    />
                                </x-filament::input.wrapper>
                                @error('cash_bank_deposit_threshold') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Banque du salon par défaut
                                </label>
                                <x-filament::input.wrapper prefix-icon="heroicon-m-building-library">
                                    <x-filament::input
                                        type="text"
                                        wire:model="default_bank_name"
                                        placeholder="Ex: Ecobank Togo"
                                    />
                                </x-filament::input.wrapper>
                                @error('default_bank_name') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Numéro de compte bancaire
                                </label>
                                <x-filament::input.wrapper prefix-icon="heroicon-m-credit-card">
                                    <x-filament::input
                                        type="text"
                                        wire:model="default_bank_account"
                                        placeholder="Ex: TG001 01234 56789012345 67"
                                    />
                                </x-filament::input.wrapper>
                                @error('default_bank_account') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Options supplémentaires -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                        <div>
                            <p class="text-xs font-bold text-gray-900 dark:text-white">Impression automatique à l'encaissement</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Générer et lancer l'impression du ticket de caisse dès la validation du paiement.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="auto_print_receipt" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                        </label>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                            Sauvegarder les paramètres de caisse
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- ========================================================================= -->
        <!-- ONGLET 3 : PROGRAMME DE FIDÉLITÉ -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'loyalty'" x-cloak class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-heart class="h-5 w-5 text-rose-500" />
                        <span>Programme & Règles de fidélité</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Définissez le seuil de fidélisation pour récompenser automatiquement vos clients réguliers.
                </x-slot>

                <form wire:submit.prevent="saveLoyalty" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Seuil de visites pour fidélité (Nombre de passages) *
                                </label>
                                <x-filament::input.wrapper prefix-icon="heroicon-m-sparkles">
                                    <x-filament::input
                                        type="number"
                                        wire:model.live="loyalty_visits_threshold"
                                        min="1"
                                        max="100"
                                        required
                                    />
                                </x-filament::input.wrapper>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                    Les clients ayant atteint ou dépassé ce nombre de visites recevront le badge VIP / Client Fidèle.
                                </p>
                                @error('loyalty_visits_threshold') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20">
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">Notification promotionnelle automatique</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Alerter ou proposer une réduction spéciale au client lors de l'atteinte du seuil.
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                                    <input type="checkbox" wire:model.live="loyalty_auto_notify" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-amber-500"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Récapitulatif dynamique du programme -->
                        <div class="rounded-2xl p-5 border border-rose-200 dark:border-rose-950/40 bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500 text-white shadow-xs">
                                        <x-heroicon-m-gift class="h-4 w-4" />
                                    </div>
                                    <h4 class="font-bold text-sm text-rose-950 dark:text-rose-200">Aperçu du statut fidélité</h4>
                                </div>

                                <div class="space-y-2 text-xs text-gray-700 dark:text-gray-300">
                                    <div class="flex justify-between py-1.5 border-b border-rose-200/50 dark:border-rose-900/30">
                                        <span>Seuil requis :</span>
                                        <span class="font-bold text-rose-600 dark:text-rose-400">{{ $loyalty_visits_threshold }} visites terminées</span>
                                    </div>
                                    <div class="flex justify-between py-1.5 border-b border-rose-200/50 dark:border-rose-900/30">
                                        <span>Offre automatique :</span>
                                        <span class="font-bold">{{ $loyalty_auto_notify ? 'Activée' : 'Désactivée' }}</span>
                                    </div>
                                    <div class="flex justify-between py-1.5">
                                        <span>Badge client :</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300">
                                            ★ Membre Privilège
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-4 italic">
                                * Vous pouvez également créer des règles de points avancées dans le cluster Marketing & Promotions.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                            Sauvegarder les règles de fidélité
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <!-- ========================================================================= -->
        <!-- ONGLET 4 : TICKETS & IMPRESSION (AVEC APERÇU THERMIQUE EN DIRECT) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'tickets'" x-cloak class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-printer class="h-5 w-5 text-amber-500" />
                        <span>Modèle de ticket & Impression Thermique</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Personnalisez les mentions de vos tickets et visualisez le rendu exact en temps réel.
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Formulaire de configuration (Gauche - 7 cols) -->
                    <form wire:submit.prevent="saveReceipt" class="lg:col-span-7 space-y-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                En-tête du ticket (Nom, slogan, adresse)
                            </label>
                            <x-filament::input.wrapper>
                                <textarea
                                    wire:model.live.debounce.300ms="receipt_header"
                                    rows="4"
                                    class="w-full text-xs font-mono rounded-lg border-0 bg-transparent p-2.5 text-gray-900 dark:text-white focus:ring-0"
                                    placeholder="Nom du salon&#10;Adresse&#10;Téléphone"
                                ></textarea>
                            </x-filament::input.wrapper>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Chaque saut de ligne est reproduit fidèlement sur le ticket imprimé.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Pied de page du ticket (Remerciements, mentions légales, wifi)
                            </label>
                            <x-filament::input.wrapper>
                                <textarea
                                    wire:model.live.debounce.300ms="receipt_footer"
                                    rows="3"
                                    class="w-full text-xs font-mono rounded-lg border-0 bg-transparent p-2.5 text-gray-900 dark:text-white focus:ring-0"
                                    placeholder="Merci de votre fidélité !&#10;À très bientôt !"
                                ></textarea>
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Choix largeur papier -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Largeur du rouleau d'impression thermique
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-between transition-all {{ $receipt_printer_width === '58' ? 'border-amber-500 bg-amber-500/5' : 'border-gray-200 dark:border-gray-800' }}">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-receipt-percent class="h-5 w-5 text-amber-500" />
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white">Format 58 mm</p>
                                            <p class="text-[10px] text-gray-500">Standard caisse compacte (POS-58)</p>
                                        </div>
                                    </div>
                                    <input type="radio" name="printer_width" value="58" wire:model.live="receipt_printer_width" class="text-amber-600 focus:ring-amber-500" />
                                </label>

                                <label class="p-3 rounded-xl border-2 cursor-pointer flex items-center justify-between transition-all {{ $receipt_printer_width === '80' ? 'border-amber-500 bg-amber-500/5' : 'border-gray-200 dark:border-gray-800' }}">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-receipt-percent class="h-5 w-5 text-amber-500" />
                                        <div>
                                            <p class="text-xs font-bold text-gray-900 dark:text-white">Format 80 mm</p>
                                            <p class="text-[10px] text-gray-500">Grand format thermique (POS-80)</p>
                                        </div>
                                    </div>
                                    <input type="radio" name="printer_width" value="80" wire:model.live="receipt_printer_width" class="text-amber-600 focus:ring-amber-500" />
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                                Sauvegarder le modèle de ticket
                            </x-filament::button>
                        </div>
                    </form>

                    <!-- Aperçu Thermique Réaliste en direct (Droite - 5 cols) -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-start">
                        <div class="w-full flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                <x-heroicon-m-eye class="h-4 w-4 text-amber-500" />
                                Aperçu thermique en direct ({{ $receipt_printer_width }}mm)
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 font-medium">
                                ● Temps réel
                            </span>
                        </div>

                        <!-- Conteneur Ticket Thermique -->
                        <div 
                            class="receipt-container w-full bg-white text-gray-900 font-mono text-xs rounded-xl shadow-xl border-2 border-dashed border-amber-300/80 p-5 transition-all duration-300"
                            style="max-width: {{ $receipt_printer_width === '58' ? '260px' : '340px' }}; min-height: 380px;"
                        >
                            <!-- En-tête -->
                            <div class="text-center font-bold whitespace-pre-line leading-relaxed text-gray-900 text-xs mb-2">
                                {{ $receipt_header ?: "💈 BarberShop Pro\n123 Rue de la République" }}
                            </div>

                            <div class="border-t border-dashed border-gray-400 my-2"></div>

                            <div class="text-center text-[10px] text-gray-600 mb-1">
                                Ticket #TKT-{{ date('Ymd') }}-042
                            </div>
                            <div class="text-center text-[10px] text-gray-600 mb-2">
                                Date : {{ date('d/m/Y H:i') }} | Caisse 01
                            </div>

                            <div class="border-t border-dashed border-gray-400 my-2"></div>

                            <!-- Lignes de prestations exemples -->
                            <div class="space-y-1.5 text-[11px]">
                                <div class="flex justify-between items-center">
                                    <span class="truncate">1x Coupe Dégradé Homme</span>
                                    <span class="font-semibold shrink-0">3 500</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="truncate">1x Traçage Barbe & Soin</span>
                                    <span class="font-semibold shrink-0">2 000</span>
                                </div>
                                <div class="flex justify-between items-center text-gray-600 text-[10px]">
                                    <span class="truncate">1x Huile Barbe Premium</span>
                                    <span class="shrink-0">1 500</span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-gray-400 my-2.5"></div>

                            <!-- Total -->
                            <div class="space-y-1">
                                <div class="flex justify-between items-center font-extrabold text-sm">
                                    <span>TOTAL</span>
                                    <span>7 000 {{ $currency }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-gray-600">
                                    <span>Mode de règlement :</span>
                                    <span class="font-bold">Espèces</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-gray-600">
                                    <span>Montant reçu :</span>
                                    <span>10 000 {{ $currency }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[10px] text-gray-600">
                                    <span>Monnaie rendue :</span>
                                    <span>3 000 {{ $currency }}</span>
                                </div>
                            </div>

                            <div class="border-t border-dashed border-gray-400 my-3"></div>

                            <!-- Pied de page -->
                            <div class="text-center font-medium whitespace-pre-line text-[10px] text-gray-600 leading-relaxed">
                                {{ $receipt_footer ?: "Merci de votre visite !\nÀ bientôt !" }}
                            </div>

                            <div class="mt-3 text-center text-[9px] text-gray-400">
                                *** SYSTÈME BARBERSHOP PRO ***
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- ========================================================================= -->
        <!-- ONGLET 5 : UTILISATEURS & RÔLES -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'users'" x-cloak class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-user-group class="h-5 w-5 text-amber-500" />
                            <span>Comptes Utilisateurs & Droits</span>
                        </div>
                    </div>
                </x-slot>
                <x-slot name="headerEnd">
                    <x-filament::button wire:click="openCreateUserModal" color="primary" icon="heroicon-m-plus" size="sm">
                        Nouvel utilisateur
                    </x-filament::button>
                </x-slot>
                <x-slot name="description">
                    Gérez les accès, rôles d'administration, caissiers et coiffeurs de l'établissement.
                </x-slot>

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Utilisateur</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Téléphone</th>
                                <th class="py-3 px-4">Statut</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                            @forelse($users as $user)
                                @php
                                    $initials = $user->initials();
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white font-bold text-xs shadow-xs">
                                                {{ $initials }}
                                            </div>
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                                    <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $user->phone ?: '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if($user->active)
                                            <x-filament::badge color="success" icon="heroicon-m-check-circle" size="xs">
                                                Actif
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="danger" icon="heroicon-m-x-circle" size="xs">
                                                Inactif
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <x-filament::button
                                                wire:click="openEditUserModal({{ $user->id }})"
                                                color="gray"
                                                size="xs"
                                                icon="heroicon-m-pencil-square"
                                            >
                                                Modifier
                                            </x-filament::button>

                                            <x-filament::button
                                                wire:click="toggleUserActive({{ $user->id }})"
                                                :color="$user->active ? 'warning' : 'success'"
                                                size="xs"
                                                outlined
                                            >
                                                {{ $user->active ? 'Désactiver' : 'Activer' }}
                                            </x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-500">
                                        Aucun utilisateur trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        <!-- ========================================================================= -->
        <!-- ONGLET 6 : JOURNAL D'ACTIVITÉ (TIMELINE AUDIT) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'activity'" x-cloak class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="h-5 w-5 text-amber-500" />
                        <span>Journal d'activité du système</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Traçabilité en temps réel des actions effectuées par les collaborateurs.
                </x-slot>

                <div class="space-y-4">
                    <!-- Filtres -->
                    <div class="flex items-center justify-between gap-4 flex-wrap pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div class="w-64">
                            <x-filament::input.wrapper size="sm">
                                <x-filament::input.select wire:model.live="activityEntityFilter">
                                    <option value="all">Toutes les entités</option>
                                    <option value="appointment">Rendez-vous</option>
                                    <option value="sale">Ventes & Encaissements</option>
                                    <option value="product">Produits & Stocks</option>
                                    <option value="client">Clients</option>
                                    <option value="user">Utilisateurs</option>
                                    <option value="setting">Paramètres système</option>
                                    <option value="expense">Dépenses</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ count($activityLogs) }} événement(s) récent(s)
                        </span>
                    </div>

                    <!-- Timeline des logs -->
                    @if(count($activityLogs) === 0)
                        <div class="py-12 text-center text-gray-400">
                            <x-heroicon-o-document-text class="h-10 w-10 mx-auto mb-2 opacity-40" />
                            <p class="text-xs">Aucune activité enregistrée pour ce filtre.</p>
                        </div>
                    @else
                        <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-800">
                            @foreach($activityLogs as $log)
                                @php
                                    $actionColor = match($log->action) {
                                        'create' => 'bg-emerald-500 text-white',
                                        'update' => 'bg-amber-500 text-white',
                                        'delete' => 'bg-rose-500 text-white',
                                        default => 'bg-gray-500 text-white',
                                    };
                                    $actionIcon = match($log->entity) {
                                        'appointment' => 'heroicon-m-calendar',
                                        'sale' => 'heroicon-m-shopping-bag',
                                        'product' => 'heroicon-m-cube',
                                        'client' => 'heroicon-m-user',
                                        'user' => 'heroicon-m-user-group',
                                        'setting' => 'heroicon-m-cog-6-tooth',
                                        'expense' => 'heroicon-m-banknotes',
                                        default => 'heroicon-m-bolt',
                                    };
                                @endphp
                                <div class="relative flex items-start gap-3">
                                    <div class="absolute -left-6 mt-0.5 flex h-5 w-5 items-center justify-center rounded-full {{ $actionColor }} shadow-xs ring-4 ring-white dark:ring-gray-900">
                                        <x-dynamic-component :component="$actionIcon" class="h-3 w-3" />
                                    </div>
                                    <div class="flex-1 min-w-0 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800/60">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                                {{ $log->details ?: ($log->action . ' ' . $log->entity) }}
                                            </p>
                                            <span class="text-[10px] text-gray-400 tabular-nums shrink-0">
                                                {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                            </span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                            <span>Par : <strong class="text-gray-700 dark:text-gray-300">{{ $log->user?->name ?? 'Système' }}</strong></span>
                                            @if($log->entity)
                                                <span>•</span>
                                                <x-filament::badge color="gray" size="xs">
                                                    {{ $log->entity }}
                                                </x-filament::badge>
                                            @endif
                                            <span>•</span>
                                            <span>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODALE CRÉATION / MODIFICATION D'UTILISATEUR -->
    <!-- ========================================================================= -->
    @if($isUserModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-user class="h-5 w-5 text-amber-500" />
                        {{ $editingUserId ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}
                    </h3>
                    <button wire:click="closeUserModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form wire:submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nom complet *
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model="userName"
                                placeholder="Jean Dupont"
                                required
                            />
                        </x-filament::input.wrapper>
                        @error('userName') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Adresse Email *
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="email"
                                wire:model="userEmail"
                                placeholder="jean.dupont@salon.com"
                                required
                            />
                        </x-filament::input.wrapper>
                        @error('userEmail') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Téléphone
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model="userPhone"
                                placeholder="+228 90 00 00 00"
                            />
                        </x-filament::input.wrapper>
                        @error('userPhone') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Mot de passe {{ $editingUserId ? '(laisser vide pour ne pas changer)' : '*' }}
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="userPassword"
                                placeholder="••••••••"
                                :required="!$editingUserId"
                            />
                        </x-filament::input.wrapper>
                        @error('userPassword') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="userActiveCheck" wire:model="userActive" class="rounded text-amber-600 focus:ring-amber-500">
                        <label for="userActiveCheck" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Compte utilisateur actif
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <x-filament::button type="button" color="gray" wire:click="closeUserModal">
                            Annuler
                        </x-filament::button>
                        <x-filament::button type="submit" color="primary">
                            {{ $editingUserId ? 'Mettre à jour' : 'Créer l\'utilisateur' }}
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
