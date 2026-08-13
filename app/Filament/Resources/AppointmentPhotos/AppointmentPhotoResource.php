<?php

namespace App\Filament\Resources\AppointmentPhotos;

use App\Filament\Resources\AppointmentPhotos\Schemas\AppointmentPhotoForm;
use App\Filament\Resources\AppointmentPhotos\Tables\AppointmentPhotosTable;
use App\Models\AppointmentPhoto;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
        return AppointmentPhotoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentPhotosTable::configure($table);
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
