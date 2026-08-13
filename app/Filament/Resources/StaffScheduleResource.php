<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffScheduleResource\Pages;
use App\Models\StaffSchedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StaffScheduleResource extends Resource
{
    protected static ?string $model = StaffSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 32;

    protected static ?string $modelLabel = 'Emploi du temps';

    protected static ?string $pluralModelLabel = 'Emplois du temps';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Emploi du temps')
                    ->schema([
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('dayOfWeek')
                            ->label('Jour de la semaine')
                            ->options([
                                0 => 'Dimanche',
                                1 => 'Lundi',
                                2 => 'Mardi',
                                3 => 'Mercredi',
                                4 => 'Jeudi',
                                5 => 'Vendredi',
                                6 => 'Samedi',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TimePicker::make('startTime')
                            ->label('Heure de début'),
                        Forms\Components\TimePicker::make('endTime')
                            ->label('Heure de fin'),
                        Forms\Components\Toggle::make('isDayOff')
                            ->label('Jour de repos')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barber.firstName')
                    ->label('Coiffeur')
                    ->formatStateUsing(fn ($record) => $record->barber?->firstName.' '.$record->barber?->lastName)
                    ->searchable(),
                Tables\Columns\TextColumn::make('dayOfWeek')
                    ->label('Jour')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Dimanche',
                        1 => 'Lundi',
                        2 => 'Mardi',
                        3 => 'Mercredi',
                        4 => 'Jeudi',
                        5 => 'Vendredi',
                        6 => 'Samedi',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('startTime')
                    ->label('Début'),
                Tables\Columns\TextColumn::make('endTime')
                    ->label('Fin'),
                Tables\Columns\IconColumn::make('isDayOff')
                    ->label('Repos')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('barberId')
                    ->label('Coiffeur')
                    ->relationship('barber', 'firstName')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffSchedules::route('/'),
            'create' => Pages\CreateStaffSchedule::route('/create'),
            'edit' => Pages\EditStaffSchedule::route('/{record}/edit'),
        ];
    }
}
