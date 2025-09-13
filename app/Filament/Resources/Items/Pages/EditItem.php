<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use Filament\Actions\DeleteAction;
use App\Models\StockMovement;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); //Same as createitem.php
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $additional = (int) ($this->form->getRawState()['additional_stock'] ?? 0);

        if ($additional > 0) {
            StockMovement::create([
                'item_id' => $this->record->id,
                'type' => 'in',
                'qty' => $additional,
                'reason' => 'Manual stock add from edit page',
            ]);
        }

        // ensure we don’t try to save this phantom field on items table
        unset($data['additional_stock']);

        return $data;
    }

    protected function afterSave(): void
    {
        $qty = (int) ($this->form->getState()['add_qty'] ?? 0);

        if ($qty > 0) {
            \App\Models\StockMovement::create([
                'item_id' => $this->record->id,
                'type' => 'in',
                'qty' => $qty,
                'reason' => 'Manual add from Edit Item',
            ]);
        }
    }



}
