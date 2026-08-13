<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\PurchaseOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 35;

    protected static ?string $modelLabel = 'Commande d\'achat';

    protected static ?string $pluralModelLabel = 'Commandes d\'achat';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la commande')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\Select::make('supplierId')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('orderDate')
                            ->label('Date de commande')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('expectedDate')
                            ->label('Date prévue'),
                        Forms\Components\DatePicker::make('receivedDate')
                            ->label('Date de réception'),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'ordered' => 'Commandée',
                                'received' => 'Reçue',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('totalAmount')
                            ->label('Montant total (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('paidAmount')
                            ->label('Montant payé (FCFA)')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orderDate')
                    ->label('Date de commande')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expectedDate')
                    ->label('Date prévue')
                    ->date('d/m/Y')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('totalAmount')
                    ->label('Montant total')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'ordered' => 'info',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'ordered' => 'Commandée',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'ordered' => 'Commandée',
                        'received' => 'Reçue',
                        'cancelled' => 'Annulée',
                    ]),
                Tables\Filters\SelectFilter::make('supplierId')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name'),
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
            ->defaultSort('orderDate', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
