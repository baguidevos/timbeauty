@php
    try {
        $shopName = \App\Models\Setting::get('shop_name', 'BarberShop Pro') ?: 'BarberShop Pro';
    } catch (\Throwable $e) {
        $shopName = 'BarberShop Pro';
    }
    $isDarkMode = $isDark ?? false;
    $hasLogoImg = file_exists(public_path('favicon/logo.png'));
    $hasLogoSvg = file_exists(public_path('favicon/favicon.svg'));
@endphp

@if($isDarkMode)
    {{-- Version Dark Mode Dédiée (Ambiance Nocturne & Dorée Prestigieuse) --}}
    <div class="flex items-center gap-3 py-1 group">
        @if($hasLogoImg)
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-900 p-1 shadow-lg shadow-amber-500/15 ring-1 ring-amber-500/40 transition-all duration-300 group-hover:scale-105 group-hover:ring-amber-400 group-hover:shadow-amber-500/30 overflow-hidden">
                <img src="{{ asset('favicon/logo.png') }}" alt="{{ $shopName }}" class="h-full w-full object-contain drop-shadow-sm" />
            </div>
        @else
            <!-- Emblème Barbier Dark (Fond sombre texturé + lueur dorée ambrée) -->
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-950 text-amber-400 shadow-lg shadow-amber-500/15 ring-1 ring-amber-500/40 transition-all duration-300 group-hover:scale-105 group-hover:ring-amber-400 group-hover:shadow-amber-500/30">
                <div class="absolute -inset-0.5 rounded-xl bg-gradient-to-r from-amber-500/20 to-amber-600/20 blur-xs opacity-75 pointer-events-none group-hover:opacity-100 transition-opacity"></div>
                <svg class="relative z-10 h-5 w-5 fill-current text-amber-400 transition-transform duration-300 group-hover:rotate-6 drop-shadow-[0_2px_8px_rgba(245,158,11,0.4)]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.64 7.64a4 4 0 1 0-1.41-1.41L12 10l3.77-3.77a4 4 0 1 0-1.41 1.41L12 10l-2.36-2.36ZM6 4a2 2 0 1 1-2 2 2 2 0 0 1 2-2Zm12 0a2 2 0 1 1-2 2 2 2 0 0 1 2-2Z"/>
                    <path d="M12 11.41 8.23 15.18a4 4 0 1 0 1.41 1.41L12 14.23l2.36 2.36a4 4 0 1 0 1.41-1.41L12 11.41ZM6 20a2 2 0 1 1 2-2 2 2 0 0 1-2 2Zm12 0a2 2 0 1 1 2-2 2 2 0 0 1-2 2Z"/>
                    <circle cx="12" cy="12" r="1.5"/>
                </svg>
            </div>
        @endif

        <!-- Typographie Dark -->
        <div class="flex flex-col min-w-0">
            <span class="text-base font-extrabold tracking-tight text-white truncate transition-colors duration-200 group-hover:text-amber-400">
                {{ $shopName }}
            </span>
            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-400/90 flex items-center gap-1.5">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)] animate-pulse"></span>
                Barbershop & Coiffure
            </span>
        </div>
    </div>
@else
    {{-- Version Light Mode --}}
    <div class="flex items-center gap-3 py-1 group">
        @if($hasLogoImg)
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white p-1 shadow-lg shadow-amber-500/20 ring-1 ring-amber-400/40 transition-all duration-300 group-hover:scale-105 group-hover:shadow-amber-500/30 overflow-hidden">
                <img src="{{ asset('favicon/logo.png') }}" alt="{{ $shopName }}" class="h-full w-full object-contain drop-shadow-xs" />
            </div>
        @else
            <!-- Emblème Barbier Light -->
            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 via-amber-600 to-amber-700 text-white shadow-lg shadow-amber-500/25 ring-1 ring-amber-400/30 transition-all duration-300 group-hover:scale-105 group-hover:shadow-amber-500/40">
                <div class="absolute inset-0 rounded-xl bg-gradient-to-tr from-white/0 via-white/10 to-white/30 opacity-80 pointer-events-none"></div>
                <svg class="relative z-10 h-5 w-5 fill-current text-white transition-transform duration-300 group-hover:rotate-6 drop-shadow-xs" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.64 7.64a4 4 0 1 0-1.41-1.41L12 10l3.77-3.77a4 4 0 1 0-1.41 1.41L12 10l-2.36-2.36ZM6 4a2 2 0 1 1-2 2 2 2 0 0 1 2-2Zm12 0a2 2 0 1 1-2 2 2 2 0 0 1 2-2Z"/>
                    <path d="M12 11.41 8.23 15.18a4 4 0 1 0 1.41 1.41L12 14.23l2.36 2.36a4 4 0 1 0 1.41-1.41L12 11.41ZM6 20a2 2 0 1 1 2-2 2 2 0 0 1-2 2Zm12 0a2 2 0 1 1 2-2 2 2 0 0 1-2 2Z"/>
                    <circle cx="12" cy="12" r="1.5"/>
                </svg>
            </div>
        @endif

        <!-- Typographie Light -->
        <div class="flex flex-col min-w-0">
            <span class="text-base font-extrabold tracking-tight text-gray-950 dark:text-white truncate transition-colors duration-200 group-hover:text-amber-600 dark:group-hover:text-amber-400">
                {{ $shopName }}
            </span>
            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-600/90 dark:text-amber-400/90 flex items-center gap-1.5">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Barbershop & Coiffure
            </span>
        </div>
    </div>
@endif
