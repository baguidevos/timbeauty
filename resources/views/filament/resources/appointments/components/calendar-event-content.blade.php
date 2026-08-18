<div class="flex flex-col h-full w-full justify-between p-1.5 text-xs select-none overflow-hidden leading-tight group cursor-pointer hover:brightness-105 transition-all">
    <!-- Top Row: Client Name & Time -->
    <div class="flex items-start justify-between gap-1">
        <div class="font-bold text-white tracking-tight truncate flex items-center gap-1">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-white/80 shrink-0"></span>
            <span x-text="event.extendedProps.clientName" class="truncate"></span>
        </div>
        <span
            x-text="event.extendedProps.formattedTime"
            class="text-[10px] font-mono font-medium text-white/90 bg-black/20 px-1 py-0.5 rounded shrink-0 shadow-xs"
        ></span>
    </div>

    <!-- Middle Row: Service Name & Price -->
    <div class="my-0.5 flex items-center justify-between gap-1 text-[11px] text-white/90">
        <div class="truncate flex items-center gap-1 font-medium">
            <span x-text="event.extendedProps.serviceName" class="truncate"></span>
        </div>
        <template x-if="event.extendedProps.price">
            <span x-text="event.extendedProps.price" class="text-[10px] font-bold text-amber-200 shrink-0"></span>
        </template>
    </div>

    <!-- Bottom Row: Status Badge & Quick Action Buttons -->
    <div class="flex items-center justify-between gap-1 pt-1 border-t border-white/20 mt-0.5">
        <!-- Status Label -->
        <span
            x-text="event.extendedProps.statusLabel"
            class="text-[9px] font-extrabold uppercase tracking-wider px-1.5 py-0.5 rounded-full bg-white/20 text-white shadow-2xs backdrop-blur-xs"
        ></span>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-1 shrink-0">
            <!-- Pending -> Confirm button -->
            <template x-if="event.extendedProps.status === 'pending'">
                <button
                    type="button"
                    title="Confirmer ce RDV"
                    @click.stop="$wire.updateAppointmentStatus(event.extendedProps.id, 'confirmed')"
                    class="px-1.5 py-0.5 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold shadow-xs hover:scale-105 transition-all flex items-center gap-0.5"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Valider</span>
                </button>
            </template>

            <!-- Confirmed -> Start button -->
            <template x-if="event.extendedProps.status === 'confirmed'">
                <button
                    type="button"
                    title="Démarrer la prestation"
                    @click.stop="$wire.updateAppointmentStatus(event.extendedProps.id, 'in_progress')"
                    class="px-1.5 py-0.5 rounded bg-sky-600 hover:bg-sky-500 text-white text-[10px] font-bold shadow-xs hover:scale-105 transition-all flex items-center gap-0.5"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Démarrer</span>
                </button>
            </template>

            <!-- In Progress -> Complete button -->
            <template x-if="event.extendedProps.status === 'in_progress'">
                <button
                    type="button"
                    title="Terminer la prestation"
                    @click.stop="$wire.updateAppointmentStatus(event.extendedProps.id, 'completed')"
                    class="px-1.5 py-0.5 rounded bg-emerald-700 hover:bg-emerald-600 text-white text-[10px] font-bold shadow-xs hover:scale-105 transition-all flex items-center gap-0.5"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Terminer</span>
                </button>
            </template>

            <!-- Completed -> Encaisser au POS (if not yet paid) -->
            <template x-if="event.extendedProps.status === 'completed' && !event.extendedProps.isPaid">
                <button
                    type="button"
                    title="Encaisser ce rendez-vous en caisse"
                    @click.stop="window.location.href = '/admin/pos?appointment=' + event.extendedProps.id"
                    class="px-1.5 py-0.5 rounded bg-amber-500 hover:bg-amber-400 text-gray-950 text-[10px] font-extrabold shadow-xs hover:scale-105 transition-all flex items-center gap-0.5 animate-pulse"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span>Encaisser</span>
                </button>
            </template>

            <!-- Completed & Paid -> Payé badge -->
            <template x-if="event.extendedProps.status === 'completed' && event.extendedProps.isPaid">
                <span
                    class="px-1.5 py-0.5 rounded bg-emerald-500/30 border border-emerald-300/40 text-white text-[9px] font-extrabold tracking-wider uppercase flex items-center gap-0.5"
                >
                    <svg class="w-3 h-3 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Payé</span>
                </span>
            </template>

            <!-- Quick Cancel Button (for pending or confirmed) -->
            <template x-if="['pending', 'confirmed'].includes(event.extendedProps.status)">
                <button
                    type="button"
                    title="Annuler ce RDV"
                    @click.stop="if(confirm('Annuler ce rendez-vous ?')) { $wire.updateAppointmentStatus(event.extendedProps.id, 'cancelled'); }"
                    class="p-0.5 rounded bg-rose-700/80 hover:bg-rose-600 text-white shadow-xs hover:scale-105 transition-all"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </template>
        </div>
    </div>
</div>
