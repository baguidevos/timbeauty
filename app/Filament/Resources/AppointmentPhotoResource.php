<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentPhotoResource\Pages;
use App\Models\AppointmentPhoto;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentPhotoResource extends Resource
{
    protected static ?string $model = AppointmentPhoto::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static string|\UnitEnum|null $navigationGroup = 'Principal';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'Photo de rendez-vous';

    protected static ?string $pluralModelLabel = 'Photos de rendez-vous';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de la photo')
                    ->schema([
                        Forms\Components\Select::make('appointmentId')
                            ->label('Rendez-vous')
                            ->relationship('appointment', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 'RDV #'.$record->id.' - '.$record->date?->format('d/m/Y'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'before' => 'Avant',
                                'after' => 'Après',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('url')
                            ->label('URL de la photo')
                            ->url()
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('caption')
                            ->label('Légende')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tags')
                            ->label('Étiquettes')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.id')
                    ->label('Rendez-vous')
                    ->formatStateUsing(fn ($record) => 'RDV #'.$record->appointmentId)
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client?->firstName.' '.$record->client?->lastName)
                    ->searchable(),
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'before' ? 'warning' : 'success')
                    ->formatStateUsing(fn (string $state): string => $state === 'before' ? 'Avant' : 'Après'),
                Tables\Columns\ImageColumn::make('url')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Légende')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'before' => 'Avant',
                        'after' => 'Après',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointmentPhotos::route('/'),
            'create' => Pages\CreateAppointmentPhoto::route('/create'),
            'view' => Pages\ViewAppointmentPhoto::route('/{record}'),
            'edit' => Pages\EditAppointmentPhoto::route('/{record}/edit'),
        ];
    }
}
