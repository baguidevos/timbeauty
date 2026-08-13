<?php

namespace App\Filament\Resources\ServiceCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceCategoryForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Section::make('Informations de la catégorie')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500),
                        TextInput::make('icon')
                            ->label('Icône')
                            ->maxLength(50),
                        TextInput::make('order')
                            ->label('Ordre')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }
}
