<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Helpers\FormatHelper;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Prestation';

    protected static ?string $pluralModelLabel = 'Prestations';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la prestation')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->label('Prix (FCFA)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('duration')
                            ->label('Durée (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('min'),
                        Forms\Components\Select::make('categoryId')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('commissionRate')
                            ->label('Taux de commission (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durée')
                    ->formatStateUsing(fn ($state) => $state.' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commissionRate')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        default => $state,
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
                Tables\Filters\SelectFilter::make('categoryId')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
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
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
