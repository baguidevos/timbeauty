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
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Transactions de caisse';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Type de mouvement')
                    ->options([
                        'deposit' => 'Apport / Dépôt de fond',
                        'withdrawal' => 'Prélèvement / Retrait',
                        'owner_contribution' => 'Apport / Avance Propriétaire',
                        'owner_refund' => 'Remboursement Propriétaire',
                        'bank_deposit' => 'Dépôt en Banque (Écrémage)',
                        'adjustment' => 'Ajustement inventaire',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('amount')
                    ->label('Montant (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('description')
                    ->label('Description / Motif')
                    ->maxLength(255),
                Forms\Components\TextInput::make('referenceId')
                    ->label('Référence / N° Pièce')
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
                    ->color(fn (string $state): string => match ($state) {
                        'sale', 'deposit', 'in' => 'success',
                        'owner_contribution' => 'primary',
                        'bank_deposit' => 'info',
                        'owner_refund', 'withdrawal' => 'warning',
                        'expense', 'out' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale' => 'Vente / Encaissement',
                        'expense' => 'Dépense',
                        'deposit' => 'Apport de caisse',
                        'withdrawal' => 'Retrait',
                        'owner_contribution' => 'Avance Propriétaire',
                        'owner_refund' => 'Remboursement Proprio',
                        'bank_deposit' => 'Dépôt en Banque',
                        'adjustment' => 'Ajustement',
                        'in' => 'Entrée',
                        'out' => 'Sortie',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('referenceId')
                    ->label('Réf.')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Effectué par')
                    ->placeholder('Système'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouveau mouvement')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['createdBy'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
