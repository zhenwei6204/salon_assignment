<?php

namespace App\Filament\Resources\Items\Items\Pages;

use App\Filament\Resources\Items\Items\ItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function getRedirectUrl(): string
    {
        // after creating an item, go back to the index
        return static::getResource()::getUrl('index');
    }
}
