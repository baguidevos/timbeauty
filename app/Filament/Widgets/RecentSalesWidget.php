<?php

namespace App\Filament\Widgets;

use App\Helpers\FormatHelper;
use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSalesWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with(['client'])
                    ->latest()
                    ->limit(5)
            )
            ->heading('Dernières ventes')
            ->emptyStateHeading('Aucune vente')
            ->emptyStateDescription('Les ventes récentes apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->columns([
                Tables\Columns\TextColumn::make('client.fullName')
                    ->label('Client')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(15),
                Tables\Columns\TextColumn::make('discountAmount')
                    ->label('Remise')
                    ->formatStateUsing(fn ($state) => $state > 0 ? '- '.FormatHelper::formatFCFA($state) : null)
                    ->color('danger')
                    ->badge(fn ($state) => $state > 0)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total Net')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->weight('medium')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('paymentMethod')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Espèces',
                        'tmoney' => 'TMoney',
                        'flooz' => 'Flooz',
                        'card' => 'Carte',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'tmoney' => 'info',
                        'flooz' => 'warning',
                        'card' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ]);
    }
}
