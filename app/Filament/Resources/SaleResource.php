<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Sale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Vente';

    protected static ?string $pluralModelLabel = 'Ventes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la vente')
                    ->schema([
                        Forms\Components\Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('paymentMethod')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'tmoney' => 'TMoney',
                                'flooz' => 'Flooz',
                                'card' => 'Carte',
                                'transfer' => 'Virement',
                                'other' => 'Autre',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'completed' => 'Terminée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Sous-total (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('discountAmount')
                            ->label('Remise (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('total')
                            ->label('Total (FCFA)')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°')
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client ? $record->client->firstName.' '.$record->client->lastName : '-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('paymentMethod')
                    ->label('Paiement')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Espèces',
                        'tmoney' => 'TMoney',
                        'flooz' => 'Flooz',
                        'card' => 'Carte',
                        'transfer' => 'Virement',
                        'other' => 'Autre',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ]),
                Tables\Filters\SelectFilter::make('paymentMethod')
                    ->label('Méthode de paiement')
                    ->options([
                        'cash' => 'Espèces',
                        'tmoney' => 'TMoney',
                        'flooz' => 'Flooz',
                        'card' => 'Carte',
                        'transfer' => 'Virement',
                        'other' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Coiffeur')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
