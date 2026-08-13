<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Helpers\FormatHelper;
use App\Models\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients';

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
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),
                        Forms\Components\Select::make('gender')
                            ->label('Genre')
                            ->options([
                                'male' => 'Homme',
                                'female' => 'Femme',
                                'other' => 'Autre',
                            ])
                            ->native(false),
                        Forms\Components\DatePicker::make('birthDate')
                            ->label('Date de naissance'),
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Fidélité')
                    ->schema([
                        Forms\Components\Toggle::make('isLoyal')
                            ->label('Client fidèle'),
                        Forms\Components\TextInput::make('loyaltyPoints')
                            ->label('Points de fidélité')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('loyaltyTierId')
                            ->label('Niveau de fidélité')
                            ->relationship('loyaltyTier', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(3),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('totalVisits')
                    ->label('Visites')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalSpent')
                    ->label('Total dépensé')
                    ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\IconColumn::make('isLoyal')
                    ->label('Fidèle')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loyaltyPoints')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('isLoyal')
                    ->label('Client fidèle'),
                Tables\Filters\SelectFilter::make('loyaltyTierId')
                    ->label('Niveau de fidélité')
                    ->relationship('loyaltyTier', 'name'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AppointmentsRelationManager::class,
            RelationManagers\SalesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
