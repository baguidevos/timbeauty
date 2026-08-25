<?php

namespace App\Filament\Resources\LoyaltyRules;

use App\Filament\Clusters\Marketing;
use App\Filament\Resources\LoyaltyRules\Schemas\LoyaltyRuleForm;
use App\Filament\Resources\LoyaltyRules\Tables\LoyaltyRulesTable;
use App\Models\LoyaltyRule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LoyaltyRuleResource extends Resource
{
    protected static ?string $model = LoyaltyRule::class;

    protected static ?string $cluster = Marketing::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Règles de Points';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Règle de fidélité';

    protected static ?string $pluralModelLabel = 'Programme de Fidélité';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyRules::route('/'),
        ];
    }
}
