<?php

namespace App\Filament\Components;

use App\Helpers\FormatHelper;
use Filament\Tables\Columns\TextColumn;

class MoneyColumn
{
    /**
     * Create a TextColumn formatted for FCFA currency display.
     */
    public static function make(string $name): TextColumn
    {
        return TextColumn::make($name)
            ->label('Montant')
            ->money('XOF', 0, 'fr')
            ->formatStateUsing(fn ($state) => FormatHelper::formatFCFA($state))
            ->sortable()
            ->alignEnd();
    }
}
