<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Product;
use App\Models\Service;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations de la vente')
                    ->schema([
                        Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName.' ('.$record->phone.')')
                            ->searchable()
                            ->preload()
                            ->placeholder('👤 Client anonyme (Walk-in)'),

                        Select::make('barberId')
                            ->label('Coiffeur / Barbier assigné')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->placeholder('✂️ Aucun coiffeur assigné'),

                        Select::make('paymentMethod')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'tmoney' => 'TMoney',
                                'flooz' => 'Flooz',
                                'card' => 'Carte bancaire',
                                'transfer' => 'Virement',
                                'other' => 'Autre',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->label('Statut de la vente')
                            ->options([
                                'completed' => 'Terminée',
                                'pending' => 'En attente',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('completed')
                            ->required()
                            ->native(false),

                        Textarea::make('notes')
                            ->label('Notes internes / Remarques')
                            ->placeholder('Remarques sur la vente, demandes particulières...')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Récapitulatif Financier')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Sous-total Brut (FCFA)')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('discountAmount')
                            ->label('Remise Globale (FCFA)')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                static::updateTotals($get, $set);
                            }),

                        TextInput::make('total')
                            ->label('Total Net à Payer (FCFA)')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),

                        Placeholder::make('total_display')
                            ->hiddenLabel()
                            ->content(function (Get $get) {
                                $subtotal = (float) ($get('subtotal') ?: 0);
                                $discount = (float) ($get('discountAmount') ?: 0);
                                $total = (float) ($get('total') ?: max(0, $subtotal - $discount));

                                return new HtmlString('
                                    <div class="flex items-center justify-between rounded-xl bg-amber-500/10 border border-amber-500/20 px-5 py-3.5 dark:bg-amber-500/15">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Sous-total : '.number_format($subtotal, 0, ',', ' ').' FCFA | Remise : -'.number_format($discount, 0, ',', ' ').' FCFA</p>
                                            <p class="text-base font-extrabold text-amber-900 dark:text-amber-300">Total Net Facture</p>
                                        </div>
                                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400">'.number_format($total, 0, ',', ' ').' FCFA</span>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Articles de la vente (Prestations & Produits)')
                    ->description('Gérez les lignes de prestations et de produits vendus.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Lignes du ticket')
                            ->relationship('items')
                            ->addActionLabel('+ Ajouter une prestation ou un produit')
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->cloneable(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                static::updateTotals($get, $set);
                            })
                            ->columns(12)
                            ->schema([
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'service' => '✂️ Prestation',
                                        'product' => '🧴 Produit',
                                    ])
                                    ->default('service')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set): void {
                                        $set('itemId', null);
                                        $set('name', '');
                                        $set('unitPrice', 0);
                                        $set('total', 0);
                                    })
                                    ->columnSpan(3),

                                Select::make('itemId')
                                    ->label('Désignation')
                                    ->options(function (Get $get) {
                                        $type = $get('type') ?? 'service';
                                        if ($type === 'service') {
                                            return Service::where('status', 'active')
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }

                                        return Product::where('status', 'active')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (Product $p) => [
                                                $p->id => "{$p->name} ({$p->sellingPrice} FCFA — Stock: {$p->stockQuantity})",
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $type = $get('type') ?? 'service';
                                        if ($type === 'service') {
                                            $service = Service::find($state);
                                            if ($service) {
                                                $set('name', $service->name);
                                                $set('unitPrice', (float) $service->price);
                                            }
                                        } else {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('name', $product->name);
                                                $set('unitPrice', (float) $product->sellingPrice);
                                            }
                                        }

                                        $qty = (int) ($get('quantity') ?: 1);
                                        $price = (float) ($get('unitPrice') ?: 0);
                                        $discount = (float) ($get('discount') ?: 0);
                                        $set('total', max(0, ($qty * $price) - $discount));
                                    })
                                    ->columnSpan(4),

                                TextInput::make('quantity')
                                    ->label('Qté')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        $qty = (int) ($state ?: 1);
                                        $price = (float) ($get('unitPrice') ?: 0);
                                        $discount = (float) ($get('discount') ?: 0);
                                        $set('total', max(0, ($qty * $price) - $discount));
                                    })
                                    ->columnSpan(1),

                                TextInput::make('unitPrice')
                                    ->label('P.U. (FCFA)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        $qty = (int) ($get('quantity') ?: 1);
                                        $price = (float) ($state ?: 0);
                                        $discount = (float) ($get('discount') ?: 0);
                                        $set('total', max(0, ($qty * $price) - $discount));
                                    })
                                    ->columnSpan(2),

                                TextInput::make('discount')
                                    ->label('Remise')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        $qty = (int) ($get('quantity') ?: 1);
                                        $price = (float) ($get('unitPrice') ?: 0);
                                        $discount = (float) ($state ?: 0);
                                        $set('total', max(0, ($qty * $price) - $discount));
                                    })
                                    ->columnSpan(2),

                                Select::make('status')
                                    ->label('État de la ligne')
                                    ->options([
                                        'completed' => 'Normal / Effectué',
                                        'returned' => '📦 Retourné (Produit)',
                                        'cancelled' => '❌ Annulé (Prestation)',
                                    ])
                                    ->default('completed')
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(4),

                                TextInput::make('cancelReason')
                                    ->label('Motif (si retour ou annulation)')
                                    ->placeholder('Ex: Insatisfaction, produit défectueux, erreur...')
                                    ->maxLength(255)
                                    ->columnSpan(8),

                                Hidden::make('name'),
                                Hidden::make('total')
                                    ->default(0),
                            ])
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        $itemsDiscount = 0;

        foreach ($items as $item) {
            $status = $item['status'] ?? 'completed';
            // If item is returned or cancelled, it does not count in subtotal/total
            if ($status === 'returned' || $status === 'cancelled') {
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['unitPrice'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);

            $subtotal += ($qty * $price);
            $itemsDiscount += $discount;
        }

        $globalDiscount = (float) ($get('discountAmount') ?? 0);
        $totalDiscount = max($itemsDiscount, $globalDiscount);
        $netTotal = max(0, $subtotal - $totalDiscount);

        $set('subtotal', $subtotal);
        $set('total', $netTotal);
    }
}
