<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la notification')
                    ->schema([
                        Forms\Components\Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'appointment_reminder' => 'Rappel rendez-vous',
                                'appointment_confirmation' => 'Confirmation rendez-vous',
                                'promotion' => 'Promotion',
                                'loyalty' => 'Fidélité',
                                'general' => 'Général',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('channel')
                            ->label('Canal')
                            ->options([
                                'sms' => 'SMS',
                                'whatsapp' => 'WhatsApp',
                                'email' => 'Email',
                                'push' => 'Push',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\Toggle::make('read')
                            ->label('Lu')
                            ->default(false),
                        Forms\Components\Toggle::make('sent')
                            ->label('Envoyé')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('sentAt')
                            ->label('Envoyé le'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client ? $record->client->firstName.' '.$record->client->lastName : '-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'appointment_reminder' => 'Rappel RDV',
                        'appointment_confirmation' => 'Confirmation RDV',
                        'promotion' => 'Promotion',
                        'loyalty' => 'Fidélité',
                        'general' => 'Général',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'push' => 'Push',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(40),
                Tables\Columns\IconColumn::make('read')
                    ->label('Lu')
                    ->boolean(),
                Tables\Columns\IconColumn::make('sent')
                    ->label('Envoyé')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('read')
                    ->label('Lu'),
                Tables\Filters\TernaryFilter::make('sent')
                    ->label('Envoyé'),
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Canal')
                    ->options([
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'push' => 'Push',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'view' => Pages\ViewNotification::route('/{record}'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
