<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarberResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Barber;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BarberResource extends Resource
{
    protected static ?string $model = Barber::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scissors';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Coiffeur';

    protected static ?string $pluralModelLabel = 'Coiffeurs';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('firstName')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('lastName')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('hireDate')
                            ->label('Date d\'embauche'),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                                'on_leave' => 'En congé',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('specialties')
                            ->label('Spécialités')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Rémunération')
                    ->schema([
                        Forms\Components\Select::make('remunerationType')
                            ->label('Type de rémunération')
                            ->options([
                                'fixed' => 'Salaire fixe',
                                'commission' => 'Commission',
                                'fixed_plus_commission' => 'Fixe + Commission',
                                'per_service' => 'Par prestation',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        Forms\Components\TextInput::make('fixedSalary')
                            ->label('Salaire fixe (FCFA)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => in_array($get('remunerationType'), ['fixed', 'fixed_plus_commission'])),
                        Forms\Components\TextInput::make('commissionRate')
                            ->label('Taux de commission (%)')
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => in_array($get('remunerationType'), ['commission', 'fixed_plus_commission'])),
                        Forms\Components\TextInput::make('perServiceRate')
                            ->label('Tarif par prestation (FCFA)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('remunerationType') === 'per_service'),
                    ])->columns(2),
                Forms\Components\Section::make('Compte utilisateur')
                    ->schema([
                        Forms\Components\Select::make('userId')
                            ->label('Utilisateur associé')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('firstName')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lastName')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Columns\TextColumn::make('remunerationType')
                    ->label('Rémunération')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Salaire fixe',
                        'commission' => 'Commission',
                        'fixed_plus_commission' => 'Fixe + Commission',
                        'per_service' => 'Par prestation',
                        default => $state,
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fixedSalary')
                    ->label('Salaire fixe')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('hireDate')
                    ->label('Date d\'embauche')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarbers::route('/'),
            'create' => Pages\CreateBarber::route('/create'),
            'view' => Pages\ViewBarber::route('/{record}'),
            'edit' => Pages\EditBarber::route('/{record}/edit'),
        ];
    }
}
