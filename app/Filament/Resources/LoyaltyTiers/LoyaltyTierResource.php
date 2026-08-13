<?php

namespace App\Filament\Resources\LoyaltyTiers;

use App\Filament\Resources\LoyaltyTiers\Schemas\LoyaltyTierForm;
use App\Filament\Resources\LoyaltyTiers\Tables\LoyaltyTiersTable;
use App\Models\LoyaltyTier;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
        return LoyaltyTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyTiersTable::configure($table);
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
