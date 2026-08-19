<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Helpers\FormatHelper;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';

    protected static ?string $title = 'Ventes';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°'),
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Sous-total')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discountAmount')
                    ->label('Remise')
                    ->formatStateUsing(fn ($state) => $state > 0 ? '- '.FormatHelper::formatFCFA($state) : '—')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->badge(fn ($state) => $state > 0)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total Net')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
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
                    }),
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
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                AttachAction::make()->label('Ajouter'),
            ])
            ->recordActions([
                ViewAction::make(),
                DetachAction::make()->label('Retirer'),
            ]);
    }
}
