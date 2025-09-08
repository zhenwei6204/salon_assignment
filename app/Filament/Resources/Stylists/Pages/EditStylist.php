<?php

// app/Filament/Resources/Stylists/Pages/EditStylist.php

namespace App\Filament\Resources\Stylists\Pages;

use App\Filament\Resources\Stylists\StylistResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStylist extends EditRecord
{
    protected static string $resource = StylistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

