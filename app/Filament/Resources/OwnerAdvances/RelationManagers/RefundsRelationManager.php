<?php

namespace App\Filament\Resources\OwnerAdvances\RelationManagers;

use App\Helpers\FormatHelper;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RefundsRelationManager extends RelationManager
{
    protected static string $relationship = 'refunds';

    protected static ?string $title = 'Historique des remboursements';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Montant remboursé (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                Select::make('paymentMethod')
                    ->label('Mode de règlement')
                    ->options([
                        'cash' => 'Espèces (Caisse)',
                        'transfer' => 'Virement bancaire',
                        'check' => 'Chèque',
                    ])
                    ->default('cash')
                    ->required()
                    ->native(false),

                Textarea::make('notes')
                    ->label('Observations')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('N°')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->weight('bold')
                    ->color('emerald')
                    ->alignEnd(),

                TextColumn::make('paymentMethod')
                    ->label('Moyen')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Espèces',
                        'transfer' => 'Virement',
                        'check' => 'Chèque',
                        default => $state,
                    }),

                TextColumn::make('cashRegister.id')
                    ->label('Session Caisse')
                    ->placeholder('Hors caisse'),

                TextColumn::make('creator.name')
                    ->label('Enregistré par')
                    ->placeholder('Système'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un remboursement')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['createdBy'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
