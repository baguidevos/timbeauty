<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Promotion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Promotion';

    protected static ?string $pluralModelLabel = 'Promotions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la promotion')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'percentage' => 'Pourcentage',
                                'fixed' => 'Montant fixe',
                                'free_service' => 'Prestation gratuite',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('value')
                            ->label('Valeur')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\DatePicker::make('startDate')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('endDate')
                            ->label('Date de fin'),
                    ])->columns(2),
                Forms\Components\Section::make('Conditions')
                    ->schema([
                        Forms\Components\TextInput::make('minVisits')
                            ->label('Visites minimum')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('forLoyalOnly')
                            ->label('Clients fidèles uniquement')
                            ->default(false),
                        Forms\Components\TextInput::make('maxUsages')
                            ->label('Utilisations maximum')
                            ->numeric(),
                        Forms\Components\TextInput::make('currentUsages')
                            ->label('Utilisations actuelles')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'expired' => 'Expirée',
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percentage' => 'Pourcentage',
                        'fixed' => 'Montant fixe',
                        'free_service' => 'Prestation gratuite',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->formatStateUsing(fn ($record) => $record->type === 'percentage' ? $record->value.'%' : FormatHelper::formatFCFA($record->value))
                    ->sortable(),
                Tables\Columns\TextColumn::make('startDate')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('endDate')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expirée',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('currentUsages')
                    ->label('Utilisations')
                    ->formatStateUsing(fn ($record) => ($record->currentUsages ?? 0).'/'.($record->maxUsages ?? '∞')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expirée',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'percentage' => 'Pourcentage',
                        'fixed' => 'Montant fixe',
                        'free_service' => 'Prestation gratuite',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'view' => Pages\ViewPromotion::route('/{record}'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
