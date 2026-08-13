<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'Journal d\'activité';

    protected static ?string $pluralModelLabel = 'Journal d\'activité';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Détails de l\'activité')
                    ->schema([
                        Forms\Components\TextInput::make('action')
                            ->label('Action')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('entity')
                            ->label('Entité')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('entityId')
                            ->label('ID entité')
                            ->numeric(),
                        Forms\Components\Textarea::make('details')
                            ->label('Détails')
                            ->maxLength(1000),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('entity')
                    ->label('Entité')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entityId')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('details')
                    ->label('Détails')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Action')
                    ->options(fn () => ActivityLog::query()->distinct()->pluck('action', 'action')->toArray()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
