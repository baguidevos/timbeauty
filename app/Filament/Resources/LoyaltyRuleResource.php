<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyRuleResource\Pages;
use App\Models\LoyaltyRule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyRuleResource extends Resource
{
    protected static ?string $model = LoyaltyRule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 52;

    protected static ?string $modelLabel = 'Règle de fidélité';

    protected static ?string $pluralModelLabel = 'Règles de fidélité';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la règle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('requiredVisits')
                            ->label('Visites requises')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Select::make('serviceId')
                            ->label('Prestation')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('discountPercentage')
                            ->label('Remise (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('message')
                            ->label('Message')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cooldownDays')
                            ->label('Jours de refroidissement')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('validityDays')
                            ->label('Validité (jours)')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
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
                Tables\Columns\TextColumn::make('requiredVisits')
                    ->label('Visites req.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Prestation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discountPercentage')
                    ->label('Remise')
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cooldownDays')
                    ->label('Refroidissement (j)')
                    ->numeric()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Active' : 'Inactive')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyRules::route('/'),
            'create' => Pages\CreateLoyaltyRule::route('/create'),
            'edit' => Pages\EditLoyaltyRule::route('/{record}/edit'),
        ];
    }
}
