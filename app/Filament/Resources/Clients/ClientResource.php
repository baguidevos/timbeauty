<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Activités Salon';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients';

    public static function getGloballySearchableAttributes(): array
    {
        return ['firstName', 'lastName', 'phone', 'whatsapp', 'email', 'address'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        /** @var Client $record */
        return $record->getFullName();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Client $record */
        $details = [];

        if ($record->phone) {
            $details['Téléphone'] = $record->phone;
        }

        if ($record->email) {
            $details['Email'] = $record->email;
        }

        $details['Visites'] = "{$record->totalVisits} visite(s)";

        if ($record->isLoyal) {
            $details['Fidélité'] = '⭐ Client Fidèle';
        }

        return $details;
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return ClientResource::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['loyaltyTier']);
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AppointmentsRelationManager::class,
            RelationManagers\SalesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ClientStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            // 'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
