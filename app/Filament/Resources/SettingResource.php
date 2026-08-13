<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Paramètre';

    protected static ?string $pluralModelLabel = 'Paramètres';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Paramètre')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Clé')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('value')
                            ->label('Valeur')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Clé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('key', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
