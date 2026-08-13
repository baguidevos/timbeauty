<?php

namespace App\Filament\Resources\ServiceCategoryResource\Pages;

use App\Filament\Resources\ServiceCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditServiceCategory extends EditRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
