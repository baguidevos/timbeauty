<?php

namespace App\Filament\Resources\CashRegisters\RelationManagers;

use App\Helpers\FormatHelper;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CashTransactionsRelationManager extends RelationManager
{
    protected static string $relationshipName = 'transactions';

    protected static ?string $title = 'Transactions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'in' => 'Entrée',
                        'out' => 'Sortie',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('amount')
                    ->label('Montant (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255),
                Forms\Components\TextInput::make('referenceId')
                    ->label('Référence')
                    ->maxLength(50),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'in' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === 'in' ? 'Entrée' : 'Sortie'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créé par'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()->label('Nouvelle transaction'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
