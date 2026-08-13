<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffAbsenceResource\Pages;
use App\Models\StaffAbsence;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class StaffAbsenceResource extends Resource
{
    protected static ?string $model = StaffAbsence::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 33;

    protected static ?string $modelLabel = 'Absence';

    protected static ?string $pluralModelLabel = 'Absences';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informations de l\'absence')
                    ->schema([
                        Forms\Components\Select::make('barberId')
                            ->label('Coiffeur')
                            ->relationship('barber', 'firstName')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->firstName.' '.$record->lastName)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('startDate')
                            ->label('Date de début')
                            ->required(),
                        Forms\Components\DatePicker::make('endDate')
                            ->label('Date de fin')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'sick' => 'Maladie',
                                'personal' => 'Personnel',
                                'vacation' => 'Congé',
                                'other' => 'Autre',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison')
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('startDate')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('endDate')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sick' => 'Maladie',
                        'personal' => 'Personnel',
                        'vacation' => 'Congé',
                        'other' => 'Autre',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Raison')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'sick' => 'Maladie',
                        'personal' => 'Personnel',
                        'vacation' => 'Congé',
                        'other' => 'Autre',
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
            ->defaultSort('startDate', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffAbsences::route('/'),
            'create' => Pages\CreateStaffAbsence::route('/create'),
            'edit' => Pages\EditStaffAbsence::route('/{record}/edit'),
        ];
    }
}
