<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de l\'utilisateur')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options([
                                'admin' => 'Administrateur',
                                'barber' => 'Coiffeur',
                                'cashier' => 'Caissier',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Toggle::make('active')
                            ->label('Actif')
                            ->default(true),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'barber' => 'info',
                        'cashier' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrateur',
                        'barber' => 'Coiffeur',
                        'cashier' => 'Caissier',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'admin' => 'Administrateur',
                        'barber' => 'Coiffeur',
                        'cashier' => 'Caissier',
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Actif'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
