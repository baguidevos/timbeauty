<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->placeholder('Sélectionner un fournisseur...'),

                TextInput::make('reference')
                    ->label('Référence')
                    ->default(fn () => 'BC-'.date('Y').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT))
                    ->placeholder('Ex: BC-2026-0001')
                    ->suffixAction(
                        Action::make('generateReference')
                            ->icon('heroicon-o-arrow-path')
                            ->tooltip('Générer une nouvelle référence')
                            ->action(function (Set $set): void {
                                $set('reference', 'BC-'.date('Y').'-'.str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT));
                            })
                    )
                    ->required()
                    ->maxLength(50),

                DatePicker::make('expectedDate')
                    ->label('Date de réception prévue')
                    ->default(fn () => now()->addDays(7))
                    ->placeholder('jj/mm/aaaa'),

                ToggleButtons::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'ordered' => 'Envoyée',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                    ])
                    ->colors([
                        'pending' => 'warning',
                        'ordered' => 'info',
                        'received' => 'success',
                        'cancelled' => 'danger',
                    ])
                    ->icons([
                        'pending' => 'heroicon-o-clock',
                        'ordered' => 'heroicon-o-paper-airplane',
                        'received' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                    ])
                    ->default('pending')
                    ->inline()
                    ->required(),

                Repeater::make('items')
                    ->label('Articles')
                    ->relationship('items')
                    ->addActionLabel('+ Ajouter un article')
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->cloneable(false)
                    ->live()
                    ->columns(12)
                    ->schema([
                        Select::make('productId')
                            ->label('Article')
                            ->options(function (Get $get) {
                                $supplierId = $get('../../supplierId');
                                $products = Product::orderBy('name')->get();

                                if (! $supplierId) {
                                    return $products->mapWithKeys(fn (Product $p) => [
                                        $p->id => "{$p->name} (Stock: {$p->stockQuantity}".($p->isLowStock() ? ' ⚠️ Bas' : '').')',
                                    ])->toArray();
                                }

                                $supplierProducts = $products->where('supplierId', $supplierId);
                                $otherProducts = $products->where('supplierId', '!=', $supplierId);

                                $groups = [];
                                if ($supplierProducts->isNotEmpty()) {
                                    $groups['Produits de ce fournisseur'] = $supplierProducts->mapWithKeys(fn (Product $p) => [
                                        $p->id => "{$p->name} (Stock: {$p->stockQuantity}".($p->isLowStock() ? ' ⚠️ Bas' : '').')',
                                    ])->toArray();
                                }
                                if ($otherProducts->isNotEmpty()) {
                                    $groups['Tous les autres articles'] = $otherProducts->mapWithKeys(fn (Product $p) => [
                                        $p->id => "{$p->name} (Stock: {$p->stockQuantity}".($p->isLowStock() ? ' ⚠️ Bas' : '').')',
                                    ])->toArray();
                                }

                                return $groups;
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('productName', $product->name);
                                        $set('unitPrice', $product->purchasePrice ?? 0);

                                        // Suggest reorder quantity if low stock
                                        $currentQty = (int) ($get('quantity') ?: 1);
                                        if ($product->isLowStock() && ($product->minStockLevel - $product->stockQuantity) > 1) {
                                            $suggestedQty = (int) ($product->minStockLevel - $product->stockQuantity);
                                            $set('quantity', $suggestedQty);
                                            $currentQty = $suggestedQty;
                                        }

                                        $unitPrice = (float) ($product->purchasePrice ?? 0);
                                        $set('totalPrice', $currentQty * $unitPrice);
                                    }
                                }
                            })
                            ->columnSpan(5),

                        TextInput::make('quantity')
                            ->label('Qté')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                $qty = (int) ($state ?: 1);
                                $unitPrice = (float) ($get('unitPrice') ?: 0);
                                $set('totalPrice', $qty * $unitPrice);
                            })
                            ->columnSpan(2),

                        TextInput::make('unitPrice')
                            ->label('Prix unit. (FCFA)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                $unitPrice = (float) ($state ?: 0);
                                $qty = (int) ($get('quantity') ?: 1);
                                $set('totalPrice', $qty * $unitPrice);
                            })
                            ->columnSpan(3),

                        Placeholder::make('line_total')
                            ->label('Total')
                            ->content(function (Get $get) {
                                $qty = (int) ($get('quantity') ?: 0);
                                $unitPrice = (float) ($get('unitPrice') ?: 0);

                                return number_format($qty * $unitPrice, 0, ',', ' ');
                            })
                            ->columnSpan(2),

                        Hidden::make('productName'),
                        Hidden::make('totalPrice'),
                    ])
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->placeholder('Remarques, instructions...')
                    ->rows(2)
                    ->columnSpanFull(),

                Placeholder::make('order_total_display')
                    ->hiddenLabel()
                    ->content(function (Get $get, Set $set) {
                        $items = $get('items') ?? [];
                        $total = 0;
                        foreach ($items as $item) {
                            $qty = (int) ($item['quantity'] ?? 0);
                            $price = (float) ($item['unitPrice'] ?? 0);
                            $total += $qty * $price;
                        }
                        $set('totalAmount', $total);

                        $formattedTotal = number_format($total, 0, ',', ' ').' FCFA';

                        return new HtmlString('
                            <div class="flex items-center justify-between rounded-xl bg-amber-500/10 border border-amber-500/20 px-5 py-3.5 dark:bg-amber-500/15">
                                <span class="text-base font-bold text-amber-800 dark:text-amber-400">Total de la commande</span>
                                <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-300">'.$formattedTotal.'</span>
                            </div>
                        ');
                    })
                    ->columnSpanFull(),

                Hidden::make('orderDate')
                    ->default(fn () => now()->toDateString()),
                Hidden::make('totalAmount')
                    ->default(0),
                Hidden::make('paidAmount')
                    ->default(0),
                Hidden::make('createdBy')
                    ->default(fn () => auth()->id()),
            ])
            ->columns(2);
    }
}
