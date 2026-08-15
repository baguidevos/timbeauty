@php
    $categories = \App\Models\ProductCategory::withCount('products')
        ->orderBy('name')
        ->get();
@endphp

<div class="mb-4 rounded-xl bg-white/50 p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900/50 dark:ring-white/10">
    <div class="flex items-center justify-between pb-2.5">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catégories</span>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                {{ $categories->count() }}
            </span>
        </div>

        <button
            type="button"
            wire:click="mountAction('createCategory')"
            class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 transition hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
        >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Ajouter</span>
        </button>
    </div>

    <div class="flex flex-wrap items-center gap-2 pt-1">
        @forelse ($categories as $category)
            <div
                wire:click="$set('tableFilters.categoryId.value', @js($this->getTableFilterState('categoryId')['value'] ?? null == $category->id ? null : (string) $category->id))"
                @class([
                    'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer select-none shadow-sm',
                    'bg-amber-50 text-amber-900 border border-amber-300 dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-700' => ($this->getTableFilterState('categoryId')['value'] ?? null) == $category->id,
                    'bg-gray-100/90 text-gray-700 hover:bg-gray-200/90 border border-gray-200/70 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-700 dark:border-gray-700' => ($this->getTableFilterState('categoryId')['value'] ?? null) != $category->id,
                ])
                title="Filtrer par {{ $category->name }}"
            >
                <svg class="h-3.5 w-3.5 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>

                <span class="font-semibold">{{ $category->name }}</span>
                <span class="text-gray-500 dark:text-gray-400">({{ $category->products_count }})</span>
            </div>
        @empty
            <span class="text-xs text-gray-500 dark:text-gray-400">Aucune catégorie créée pour le moment.</span>
        @endforelse
    </div>
</div>
