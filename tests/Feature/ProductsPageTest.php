<?php

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'active' => true,
    ]);
});

it('can filter low stock products using scope', function () {
    Product::create([
        'name' => 'Produit Stock Suffisant',
        'sellingPrice' => 5000,
        'stockQuantity' => 20,
        'minStockLevel' => 5,
        'status' => 'active',
    ]);

    $lowStockProduct = Product::create([
        'name' => 'Produit Stock Critique',
        'sellingPrice' => 3000,
        'stockQuantity' => 2,
        'minStockLevel' => 5,
        'status' => 'active',
    ]);

    $lowStock = Product::lowStock()->get();

    expect($lowStock->pluck('id'))->toContain($lowStockProduct->id)
        ->and($lowStock->count())->toBe(1);
});

it('can render products list page with low stock tab for authenticated user', function () {
    $this->actingAs($this->admin);

    Livewire::test(ListProducts::class)
        ->assertSuccessful()
        ->assertSee('Stock bas')
        ->assertSee('Tous les produits');
});
