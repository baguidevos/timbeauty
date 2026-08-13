<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyPointTransactionResource\Pages;
use App\Models\LoyaltyPointTransaction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyPointTransactionResource extends Resource
{
    protected static ?string $model = LoyaltyPointTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 55;

    protected static ?string $modelLabel = 'Transaction de points';

    protected static ?string $pluralModelLabel = 'Transactions de points';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la transaction')
                    ->schema([
                        Forms\Components\Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('points')
                            ->label('Points')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'earn' => 'Gagné',
                                'redeem' => 'Utilisé',
                                'expire' => 'Expiré',
                                'adjust' => 'Ajustement',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->maxLength(255),
                        Forms\Components\Select::make('saleId')
                            ->label('Vente associée')
                            ->relationship('sale', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 'Vente #'.$record->id)
                            ->searchable(),
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->maxLength(50),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client?->firstName.' '.$record->client?->lastName)
                    ->searchable(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->color(fn ($record) => $record->type === 'earn' ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'earn' => 'success',
                        'redeem' => 'warning',
                        'expire' => 'danger',
                        'adjust' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earn' => 'Gagné',
                        'redeem' => 'Utilisé',
                        'expire' => 'Expiré',
                        'adjust' => 'Ajustement',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'earn' => 'Gagné',
                        'redeem' => 'Utilisé',
                        'expire' => 'Expiré',
                        'adjust' => 'Ajustement',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyPointTransactions::route('/'),
            'create' => Pages\CreateLoyaltyPointTransaction::route('/create'),
            'view' => Pages\ViewLoyaltyPointTransaction::route('/{record}'),
        ];
    }
}
