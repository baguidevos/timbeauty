<?php

namespace App\Filament\Resources\CashRegisters\Schemas;

use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class CashRegisterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ViewField::make('summary')
                    ->hiddenLabel()
                    ->view('filament.resources.cash-registers.components.cash-register-summary')
                    ->columnSpanFull(),
            ]);
    }
}
