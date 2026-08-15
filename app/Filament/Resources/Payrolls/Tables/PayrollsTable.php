<?php

namespace App\Filament\Resources\Payrolls\Tables;

use App\Helpers\FormatHelper;
use App\Models\Payroll;
use App\Services\PayrollService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

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
                    ->label('Période')
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
                    ->color('success')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('bonus')
                    ->label('Primes')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->color('info')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('advances')
                    ->label('Avances')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->color('warning')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('deductions')
                    ->label('Retenues')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->color('danger')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('netSalary')
                    ->label('Net à payer')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->weight('bold')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_paid')
                    ->label('Déjà payé')
                    ->state(fn (Payroll $record) => $record->total_paid)
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->color('success')
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('remaining_amount')
                    ->label('Reste dû')
                    ->state(fn (Payroll $record) => $record->remaining_amount)
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'calculated' => 'info',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'calculated' => 'Calculé',
                        'partially_paid' => 'Partiel',
                        'paid' => 'Payé',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label('Mois')
                    ->options([
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                    ])
                    ->default(now()->month),

                Tables\Filters\SelectFilter::make('year')
                    ->label('Année')
                    ->options(array_combine(range(2024, 2030), range(2024, 2030)))
                    ->default(now()->year),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'calculated' => 'Calculé',
                        'partially_paid' => 'Partiellement payé',
                        'paid' => 'Payé',
                    ]),

                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Employé')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName),
            ])
            ->recordActions([
                // Pay salary modal action
                Action::make('pay')
                    ->label('Payer')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (Payroll $record) => $record->status !== 'paid' && $record->remaining_amount > 0)
                    ->modalHeading(fn (Payroll $record) => "Règlement du salaire — {$record->barber?->firstName} {$record->barber?->lastName}")
                    ->modalDescription(fn (Payroll $record) => sprintf(
                        'Période : %02d/%d | Net dû : %s | Déjà réglé : %s | Reste à verser : %s',
                        $record->month,
                        $record->year,
                        FormatHelper::formatFCFA($record->netSalary),
                        FormatHelper::formatFCFA($record->total_paid),
                        FormatHelper::formatFCFA($record->remaining_amount)
                    ))
                    ->form([
                        TextInput::make('amount')
                            ->label('Montant à verser (FCFA)')
                            ->numeric()
                            ->required()
                            ->default(fn (Payroll $record) => $record->remaining_amount)
                            ->minValue(1)
                            ->maxValue(fn (Payroll $record) => $record->remaining_amount),
                        Select::make('method')
                            ->label('Mode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'tmoney' => 'TMoney',
                                'flooz' => 'Flooz',
                                'transfer' => 'Virement bancaire',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false),
                        Textarea::make('notes')
                            ->label('Notes / Justificatif')
                            ->placeholder('Ex: Virement reçu, chèque n°..., reçu espèces...'),
                    ])
                    ->action(function (Payroll $record, array $data): void {
                        app(PayrollService::class)->recordPayment(
                            $record,
                            (float) $data['amount'],
                            $data['method'],
                            $data['notes'] ?? null
                        );

                        Notification::make()
                            ->title('Paiement de salaire enregistré')
                            ->body(FormatHelper::formatFCFA($data['amount'])." réglé pour {$record->barber?->firstName} {$record->barber?->lastName}")
                            ->success()
                            ->send();
                    }),

                // Quick Adjust bonus/advances/deductions
                Action::make('adjust')
                    ->label('Ajuster')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->modalHeading(fn (Payroll $record) => "Ajustement fiche de paie — {$record->barber?->firstName} {$record->barber?->lastName}")
                    ->form([
                        TextInput::make('bonus')
                            ->label('Prime / Gratification (FCFA)')
                            ->numeric()
                            ->default(fn (Payroll $record) => $record->bonus),
                        TextInput::make('advances')
                            ->label('Avances sur salaire (FCFA)')
                            ->numeric()
                            ->default(fn (Payroll $record) => $record->advances),
                        TextInput::make('deductions')
                            ->label('Retenues / Déductions (FCFA)')
                            ->numeric()
                            ->default(fn (Payroll $record) => $record->deductions),
                    ])
                    ->action(function (Payroll $record, array $data): void {
                        $record->bonus = (float) ($data['bonus'] ?? 0);
                        $record->advances = (float) ($data['advances'] ?? 0);
                        $record->deductions = (float) ($data['deductions'] ?? 0);
                        $record->netSalary = max(0, ($record->fixedSalary + $record->commissions + $record->bonus) - ($record->advances + $record->deductions));

                        if ($record->total_paid >= $record->netSalary && $record->netSalary > 0) {
                            $record->status = 'paid';
                        } elseif ($record->total_paid > 0) {
                            $record->status = 'partially_paid';
                        }
                        $record->save();

                        Notification::make()
                            ->title('Fiche de paie mise à jour')
                            ->success()
                            ->send();
                    }),

                // Payslip print modal
                Action::make('payslip')
                    ->label('Bulletin')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading('Bulletin de paie')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalContent(fn (Payroll $record): View => view(
                        'filament.resources.payrolls.payslip-modal',
                        ['record' => $record]
                    )),

                ViewAction::make()
                    ->slideOver(),
                EditAction::make()
                    ->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('month', 'desc');
    }
}
