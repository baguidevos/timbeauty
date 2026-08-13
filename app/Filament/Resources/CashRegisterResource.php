<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterResource\Pages;
use App\Filament\Resources\CashRegisterResource\RelationManagers;
use App\Helpers\FormatHelper;
use App\Models\CashRegister;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wallet';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Caisse';

    protected static ?string $pluralModelLabel = 'Caisses';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la caisse')
                    ->schema([
                        Forms\Components\TextInput::make('openingAmount')
                            ->label('Montant d\'ouverture (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('closingAmount')
                            ->label('Montant de clôture (FCFA)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'closed'),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'open' => 'Ouverte',
                                'closed' => 'Fermée',
                            ])
                            ->default('open')
                            ->required()
                            ->native(false)
                            ->live(),
                        Forms\Components\DateTimePicker::make('openedAt')
                            ->label('Ouverte le')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('closedAt')
                            ->label('Fermée le')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'closed'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°')
                    ->sortable(),
                Tables\Columns\TextColumn::make('openingAmount')
                    ->label('Ouverture')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('closingAmount')
                    ->label('Clôture')
                    ->formatStateUsing(fn ($state) => $state ? FormatHelper::formatFCFA($state) : '-')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Ouverte',
                        'closed' => 'Fermée',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('opener.name')
                    ->label('Ouvert par')
                    ->searchable(),
                Tables\Columns\TextColumn::make('openedAt')
                    ->label('Ouverte le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('closedAt')
                    ->label('Fermée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'open' => 'Ouverte',
                        'closed' => 'Fermée',
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
            ->defaultSort('openedAt', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CashTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisters::route('/'),
            'create' => Pages\CreateCashRegister::route('/create'),
            'view' => Pages\ViewCashRegister::route('/{record}'),
            'edit' => Pages\EditCashRegister::route('/{record}/edit'),
        ];
    }
}
