<?php

namespace App\Filament\Resources\Stylists\Pages;

use App\Filament\Resources\Stylists\StylistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStylists extends ListRecords
{
    protected static string $resource = StylistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
