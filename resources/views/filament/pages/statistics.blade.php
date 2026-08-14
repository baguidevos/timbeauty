<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-filament::section>
            <x-slot:name>Chiffre d'affaires du jour</x-slot:name>
            <div class="text-2xl font-bold text-primary-600">0 FCFA</div>
        </x-filament::section>
        <x-filament::section>
            <x-slot:name>Rendez-vous aujourd'hui</x-slot:name>
            <div class="text-2xl font-bold text-primary-600">0</div>
        </x-filament::section>
        <x-filament::section>
            <x-slot:name>Clients actifs</x-slot:name>
            <div class="text-2xl font-bold text-primary-600">0</div>
        </x-filament::section>
        <x-filament::section>
            <x-slot:name>Produits en stock</x-slot:name>
            <div class="text-2xl font-bold text-primary-600">0</div>
        </x-filament::section>
    </div>
    <x-filament::section>
        <x-slot:name>Statistiques détaillées</x-slot:name>
        <p class="text-gray-500">Les graphiques et statistiques détaillées seront disponibles prochainement.</p>
    </x-filament::section>
</x-filament-panels::page>
