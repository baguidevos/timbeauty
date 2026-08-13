<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Expense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Dépense';

    protected static ?string $pluralModelLabel = 'Dépenses';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la dépense')
                    ->schema([
                        Forms\Components\Select::make('categoryId')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('beneficiary')
                            ->label('Bénéficiaire')
                            ->maxLength(100),
                        Forms\Components\Select::make('paymentMethod')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'card' => 'Carte',
                                'transfer' => 'Virement',
                                'check' => 'Chèque',
                                'other' => 'Autre',
                            ])
                            ->default('cash')
                            ->native(false),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('beneficiary')
                    ->label('Bénéficiaire')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paymentMethod')
                    ->label('Paiement')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Espèces',
                        'card' => 'Carte',
                        'transfer' => 'Virement',
                        'check' => 'Chèque',
                        'other' => 'Autre',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoryId')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('paymentMethod')
                    ->label('Méthode de paiement')
                    ->options([
                        'cash' => 'Espèces',
                        'card' => 'Carte',
                        'transfer' => 'Virement',
                        'check' => 'Chèque',
                        'other' => 'Autre',
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
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
