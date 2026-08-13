<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffAttendanceResource\Pages;
use App\Models\StaffAttendance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StaffAttendanceResource extends Resource
{
    protected static ?string $model = StaffAttendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Présence';

    protected static ?string $pluralModelLabel = 'Présences';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de présence')
                    ->schema([
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('clockIn')
                            ->label('Heure d\'arrivée'),
                        Forms\Components\DateTimePicker::make('clockOut')
                            ->label('Heure de départ'),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'present' => 'Présent',
                                'late' => 'En retard',
                                'absent' => 'Absent',
                                'half_day' => 'Demi-journée',
                            ])
                            ->default('present')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('workedMinutes')
                            ->label('Minutes travaillées')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('breakMinutes')
                            ->label('Minutes de pause')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clockIn')
                    ->label('Arrivée')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clockOut')
                    ->label('Départ')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'half_day' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => 'Présent',
                        'late' => 'En retard',
                        'absent' => 'Absent',
                        'half_day' => 'Demi-journée',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('workedMinutes')
                    ->label('Travaillé (min)')
                    ->numeric()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'late' => 'En retard',
                        'absent' => 'Absent',
                        'half_day' => 'Demi-journée',
                    ]),
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
            'index' => Pages\ListStaffAttendances::route('/'),
            'create' => Pages\CreateStaffAttendance::route('/create'),
            'edit' => Pages\EditStaffAttendance::route('/{record}/edit'),
        ];
    }
}
