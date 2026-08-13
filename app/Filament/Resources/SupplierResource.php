<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Fournisseur';

    protected static ?string $pluralModelLabel = 'Fournisseurs';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations du fournisseur')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('contactName')
                            ->label('Nom du contact')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('paymentTerms')
                            ->label('Conditions de paiement')
                            ->maxLength(100),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contactName')
                    ->label('Contact')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->numeric()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
