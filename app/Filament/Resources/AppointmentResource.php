<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Principal';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Rendez-vous';

    protected static ?string $pluralModelLabel = 'Rendez-vous';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations du rendez-vous')
                    ->schema([
                        Forms\Components\Select::make('clientId')
                            ->label('Client')
                            ->relationship('client', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('serviceId')
                            ->label('Prestation')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TimePicker::make('startTime')
                            ->label('Heure début')
                            ->required(),
                        Forms\Components\TimePicker::make('endTime')
                            ->label('Heure fin')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'confirmed' => 'Confirmé',
                                'in_progress' => 'En cours',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                                'no_show' => 'Non présenté',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.firstName')
                    ->label('Client')
                    ->formatStateUsing(fn ($record) => $record->client?->firstName.' '.$record->client?->lastName)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Prestation')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('startTime')
                    ->label('Heure début')
                    ->sortable(),
                Tables\Columns\TextColumn::make('endTime')
                    ->label('Heure fin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'no_show' => 'Non présenté',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        'no_show' => 'Non présenté',
                    ]),
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Coiffeur')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName),
                Tables\Filters\SelectFilter::make('serviceId')
                    ->label('Prestation')
                    ->relationship('service', 'name'),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
