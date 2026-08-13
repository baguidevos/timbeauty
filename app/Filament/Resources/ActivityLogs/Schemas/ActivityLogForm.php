<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make('Détails de l\'activité')
                    ->schema([
                        TextInput::make('action')
                            ->label('Action')
                            ->maxLength(100),
                        TextInput::make('entity')
                            ->label('Entité')
                            ->maxLength(100),
                        TextInput::make('entityId')
                            ->label('ID entité')
                            ->numeric(),
                        Textarea::make('details')
                            ->label('Détails')
                            ->maxLength(1000),
                    ])->columns(2),
            ]);
    }
}
