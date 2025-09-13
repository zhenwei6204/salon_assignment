<?php

namespace App\Filament\Resources\Items\Items\Pages;

use App\Filament\Resources\Items\Items\ItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function getRedirectUrl(): string
    {
        
        return static::getResource()::getUrl('index');
    }
}
