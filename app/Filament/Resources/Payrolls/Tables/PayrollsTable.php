<?php

namespace App\Filament\Resources\Payrolls\Tables;

use App\Helpers\FormatHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('barber.firstName')
                    ->label('Employé')
                    ->formatStateUsing(function ($record) {
                        if (! $record->barber) {
                            return 'N/A';
                        }
                        $jobLabel = match ($record->barber->jobTitle) {
                            'barber' => 'Coiffeur',
                            'manager' => 'Gérant',
                            'receptionist' => 'Réceptionniste',
                            'cashier' => 'Caissier',
                            'cleaner' => 'Entretien',
                            default => 'Employé',
                        };

                        return "{$record->barber->firstName} {$record->barber->lastName} ({$jobLabel})";
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month')
                    ->label('Mois')
                    ->formatStateUsing(fn ($record) => sprintf('%02d/%d', $record->month, $record->year))
                    ->sortable(),
                TextColumn::make('fixedSalary')
                    ->label('Salaire fixe')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('commissions')
                    ->label('Commissions')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('netSalary')
                    ->label('Salaire net')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'calculated' => 'info',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'calculated' => 'Calculé',
                        'paid' => 'Payé',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'calculated' => 'Calculé',
                        'paid' => 'Payé',
                    ]),
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Employé')
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
            ->defaultSort('year', 'desc');
    }
}
