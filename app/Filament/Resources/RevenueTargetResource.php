<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenueTargetResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\RevenueTarget;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RevenueTargetResource extends Resource
{
    protected static ?string $model = RevenueTarget::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 25;

    protected static ?string $modelLabel = 'Objectif de revenu';

    protected static ?string $pluralModelLabel = 'Objectifs de revenus';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de l\'objectif')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'shop' => 'Salon',
                                'barber' => 'Coiffeur',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'barber'),
                        Forms\Components\TextInput::make('month')
                            ->label('Mois')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        Forms\Components\TextInput::make('year')
                            ->label('Année')
                            ->numeric()
                            ->required()
                            ->minValue(2020),
                        Forms\Components\TextInput::make('targetAmount')
                            ->label('Montant objectif (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'shop' ? 'Salon' : 'Coiffeur'),
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber ? $record->barber->firstName.' '.$record->barber->lastName : '-'),
                Tables\Columns\TextColumn::make('month')
                    ->label('Mois')
                    ->formatStateUsing(fn ($record) => sprintf('%02d/%d', $record->month, $record->year)),
                Tables\Columns\TextColumn::make('targetAmount')
                    ->label('Objectif')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'shop' => 'Salon',
                        'barber' => 'Coiffeur',
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
            ->defaultSort('year', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenueTargets::route('/'),
            'create' => Pages\CreateRevenueTarget::route('/create'),
            'edit' => Pages\EditRevenueTarget::route('/{record}/edit'),
        ];
    }
}
