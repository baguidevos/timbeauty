<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
                    ->form(function (PurchaseOrder $record): array {
                        $total = FormatHelper::formatFCFA($record->totalAmount);
                        $paid = FormatHelper::formatFCFA($record->paidAmount);
                        $remaining = FormatHelper::formatFCFA($record->remaining_amount);
                        $pct = $record->payment_percentage;

                        return [
                            Placeholder::make('payment_summary')
                                ->hiddenLabel()
                                ->content(new HtmlString('
                                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs dark:border-amber-900/50 dark:bg-amber-950/30">
                                        <div class="grid grid-cols-3 gap-2 text-center">
                                            <div>
                                                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Total</span>
                                                <div class="font-bold text-gray-900 dark:text-white">'.$total.'</div>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Déjà réglé</span>
                                                <div class="font-bold text-emerald-600 dark:text-emerald-400">'.$paid.' ('.$pct.'%)</div>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-gray-500 uppercase dark:text-gray-400">Reste dû</span>
                                                <div class="font-extrabold text-rose-600 dark:text-rose-400">'.$remaining.'</div>
                                            </div>
                                        </div>
                                    </div>
                                ')),

                            Radio::make('payment_mode')
                                ->label('Option de paiement')
                                ->options([
                                    'full' => 'Solder la totalité du reste dû ('.FormatHelper::formatFCFA($record->remaining_amount).')',
                                    'partial' => 'Verser un acompte / montant spécifique',
                                ])
                                ->default('full')
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) use ($record) {
                                    if ($state === 'full') {
                                        $set('amount', $record->remaining_amount);
                                    }
                                }),

                            TextInput::make('amount')
                                ->label('Montant du versement (FCFA)')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue($record->remaining_amount)
                                ->default($record->remaining_amount)
                                ->disabled(fn (Get $get): bool => $get('payment_mode') === 'full')
                                ->dehydrated()
                                ->helperText('Montant à ajouter au cumul des paiements.'),

                            Textarea::make('notes')
                                ->label('Référence de paiement / Notes')
                                ->placeholder('Ex: Virement bancaire réf #12345, Espèces remises au livreur...')
                                ->rows(2),
                        ];
                    })
                    ->action(function (PurchaseOrder $record, array $data): void {
                        $amountToAdd = (float) $data['amount'];
                        $record->recordPayment($amountToAdd);

                        $fresh = $record->fresh();
                        $statusText = $fresh->isFullyPaid() ? 'Commande totalement soldée (100%)' : 'Reste dû : '.FormatHelper::formatFCFA($fresh->remaining_amount);

                        Notification::make()
                            ->title('Paiement enregistré avec succès')
                            ->body('+'.FormatHelper::formatFCFA($amountToAdd)." versés. {$statusText}")
                            ->success()
                            ->send();
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
