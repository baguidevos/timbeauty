<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyTierResource\Pages;
use App\Models\LoyaltyTier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTierResource extends Resource
{
    protected static ?string $model = LoyaltyTier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Niveau de fidélité';

    protected static ?string $pluralModelLabel = 'Niveaux de fidélité';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations du niveau')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('minPoints')
                            ->label('Points minimum')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('pointsPerFCFA')
                            ->label('Points par FCFA')
                            ->numeric()
                            ->step(0.0001)
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('discountPercentage')
                            ->label('Remise (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Couleur'),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icône')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('perks')
                            ->label('Avantages')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('minPoints')
                    ->label('Points min.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pointsPerFCFA')
                    ->label('Points/FCFA')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discountPercentage')
                    ->label('Remise')
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : '-')
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Couleur')
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
                Tables\Columns\TextColumn::make('clients_count')
                    ->label('Clients')
                    ->counts('clients')
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('minPoints', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyTiers::route('/'),
            'create' => Pages\CreateLoyaltyTier::route('/create'),
            'edit' => Pages\EditLoyaltyTier::route('/{record}/edit'),
        ];
    }
}
