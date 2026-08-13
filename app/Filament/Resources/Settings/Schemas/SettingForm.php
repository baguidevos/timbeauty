<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Paramètre')
                    ->schema([
                        TextInput::make('key')
                            ->label('Clé')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('value')
                            ->label('Valeur')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }
}
