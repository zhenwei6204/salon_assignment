<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class LinkItemsToService extends Page implements HasForms
{
    use InteractsWithForms;

    // v4 expects these exact types
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-link';
    protected static \UnitEnum|string|null   $navigationGroup = 'Inventory';
    protected static ?string $title = 'Link Items to Service';

    protected string $view = 'filament.pages.link-items-to-service';

    public ?array $data = [];  

    public function mount(): void
    {
        $this->form->fill();
    }

   
    public function form($form)  
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('service_id')
                    ->label('Service')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        $json = app(\App\Services\ServiceApi::class)->listServices($search);
                        $list = $json['data'] ?? $json;
                        return collect($list)->pluck('name', 'id')->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        if (!$value) return null;
                        $svc = app(\App\Services\ServiceApi::class)->getService((int) $value);
                        return $svc['name'] ?? 'Unknown';
                    })
                    ->required(),

                \Filament\Forms\Components\Repeater::make('items')
                    ->label('Items per booking')
                    ->minItems(1)
                    ->schema([
                        \Filament\Forms\Components\Select::make('item_id')
                            ->label('Item')
                            ->options(fn () => \App\Models\Item::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('qty_per_service')
                            ->label('Qty per booking')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data'); 
    }

    public function save(): void
    {
        $state     = $this->form->getState();
        $serviceId = (int) ($state['service_id'] ?? 0);
        $rows      = $state['items'] ?? [];

        if (!$serviceId || empty($rows)) {
            Notification::make()->title('Pick a service and add at least one item.')->danger()->send();
            return;
        }

        if (! app(\App\Services\ServiceApi::class)->getService($serviceId)) {
            Notification::make()->title('Service not found via API.')->danger()->send();
            return;
        }

        foreach ($rows as $r) {
            \App\Models\ServiceItemConsumption::updateOrCreate(
                ['service_id' => $serviceId, 'item_id' => (int) $r['item_id']],
                ['qty_per_service' => (int) $r['qty_per_service']]
            );
        }

        Notification::make()->title('Linked successfully.')->success()->send();
        $this->data = [];
        $this->form->fill();
    }
}
