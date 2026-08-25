<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Resources\PurchaseOrders\Helpers\PurchaseOrderPaymentHelper;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->color('warning')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('orderDate')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('totalAmount')
                    ->label('Total Commande')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state ?? 0))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('paidAmount')
                    ->label('Montant Réglé')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state ?? 0))
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('payment_status')
                    ->label('Règlement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state, PurchaseOrder $record): string => match ($state) {
                        'paid' => 'Payée (100%)',
                        'partially_paid' => 'Partiel (Reste : '.FormatHelper::formatFCFA($record->remaining_amount).')',
                        'unpaid' => 'Non payée',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Livraison')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'ordered' => 'heroicon-o-paper-airplane',
                        'partially_received' => 'heroicon-o-cube',
                        'received' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-information-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'ordered' => 'info',
                        'partially_received' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state, PurchaseOrder $record): string => match ($state) {
                        'pending' => 'En attente',
                        'ordered' => 'Envoyée',
                        'partially_received' => 'Partiellement reçue',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->searchPlaceholder('Rechercher une commande...')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut Livraison')
                    ->options([
                        'pending' => 'En attente',
                        'ordered' => 'Envoyée',
                        'partially_received' => 'Partiellement reçue',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Statut Règlement')
                    ->options([
                        'unpaid' => 'Non payée',
                        'partially_paid' => 'Partiellement payée',
                        'paid' => 'Totalement payée',
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'unpaid' => $query->where('paidAmount', '<=', 0),
                            'paid' => $query->whereColumn('paidAmount', '>=', 'totalAmount'),
                            'partially_paid' => $query->where('paidAmount', '>', 0)->whereColumn('paidAmount', '<', 'totalAmount'),
                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
            ])
            ->recordUrl(fn ($record) => PurchaseOrderResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('recordPayment')
                    ->label('Régler / Acompte')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalWidth(Width::Medium)
                    ->modalHeading(fn (PurchaseOrder $record): string => "Règlement : {$record->reference}")
                    ->modalDescription('Enregistrez un paiement partiel (acompte) ou le règlement total de la commande.')
                    ->visible(fn (PurchaseOrder $record): bool => ! $record->isFullyPaid() && ! $record->isCancelled())
                    ->form(fn (PurchaseOrder $record): array => PurchaseOrderPaymentHelper::getPaymentSchema($record, optionalWithToggle: false))
                    ->action(function (PurchaseOrder $record, array $data): void {
                        $amountPaid = PurchaseOrderPaymentHelper::processPaymentIfPresent($record, $data, optionalWithToggle: false);

                        if ($amountPaid) {
                            $fresh = $record->fresh();
                            $statusText = $fresh->isFullyPaid() ? 'Commande totalement soldée (100%)' : 'Reste dû : '.FormatHelper::formatFCFA($fresh->remaining_amount);

                            Notification::make()
                                ->title('Paiement enregistré avec succès')
                                ->body('+'.FormatHelper::formatFCFA($amountPaid)." versés. {$statusText}")
                                ->success()
                                ->send();
                        }
                    }),

                ViewAction::make()
                    ->url(fn ($record) => PurchaseOrderResource::getUrl('view', ['record' => $record])),

                EditAction::make()
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('orderDate', 'desc');
    }
}
