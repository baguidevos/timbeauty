<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Payroll;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Fiche de paie';

    protected static ?string $pluralModelLabel = 'Fiches de paie';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la paie')
                    ->schema([
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('month')
                            ->label('Mois')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        Forms\Components\TextInput::make('year')
                            ->label('Année')
                            ->numeric()
                            ->required()
                            ->minValue(2020),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'calculated' => 'Calculé',
                                'paid' => 'Payé',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),
                    ])->columns(2),
                Forms\Components\Section::make('Montants')
                    ->schema([
                        Forms\Components\TextInput::make('fixedSalary')
                            ->label('Salaire fixe (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('commissions')
                            ->label('Commissions (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('bonus')
                            ->label('Prime (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('advances')
                            ->label('Avances (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('deductions')
                            ->label('Déductions (FCFA)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('netSalary')
                            ->label('Salaire net (FCFA)')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Mois')
                    ->formatStateUsing(fn ($record) => sprintf('%02d/%d', $record->month, $record->year))
                    ->sortable(),
                Tables\Columns\TextColumn::make('fixedSalary')
                    ->label('Salaire fixe')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('commissions')
                    ->label('Commissions')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('netSalary')
                    ->label('Salaire net')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
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
                    ->label('Coiffeur')
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'view' => Pages\ViewPayroll::route('/{record}'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
