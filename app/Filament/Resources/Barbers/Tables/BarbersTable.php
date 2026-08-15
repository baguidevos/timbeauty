<?php

namespace App\Filament\Resources\Barbers\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BarbersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('firstName')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lastName')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                TextColumn::make('jobTitle')
                    ->label('Poste / Fonction')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'barber' => 'primary',
                        'manager' => 'warning',
                        'receptionist' => 'info',
                        'cashier' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'barber' => 'Coiffeur / Barbier',
                        'manager' => 'Gérant / Manager',
                        'receptionist' => 'Réceptionniste',
                        'cashier' => 'Caissier',
                        'cleaner' => 'Entretien',
                        'other' => 'Autre',
                        default => $state ?? 'Coiffeur',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'on_leave' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'on_leave' => 'En congé',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('remunerationType')
                    ->label('Rémunération')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Salaire fixe',
                        'commission' => 'Commission',
                        'fixed_plus_commission' => 'Fixe + Commission',
                        'per_service' => 'Par prestation',
                        default => $state,
                    })
                    ->toggleable(),
                TextColumn::make('fixedSalary')
                    ->label('Salaire fixe')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->toggleable(),
                TextColumn::make('hireDate')
                    ->label('Date d\'embauche')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jobTitle')
                    ->label('Poste')
                    ->options([
                        'barber' => 'Coiffeur / Barbier',
                        'manager' => 'Gérant / Manager',
                        'receptionist' => 'Réceptionniste',
                        'cashier' => 'Caissier',
                        'cleaner' => 'Entretien',
                        'other' => 'Autre',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'on_leave' => 'En congé',
                    ]),
                Tables\Filters\SelectFilter::make('remunerationType')
                    ->label('Rémunération')
                    ->options([
                        'fixed' => 'Salaire fixe',
                        'commission' => 'Commission',
                        'fixed_plus_commission' => 'Fixe + Commission',
                        'per_service' => 'Par prestation',
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
            ->defaultSort('firstName', 'asc');
    }
}
