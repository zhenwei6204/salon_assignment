<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use App\Models\Item;
use App\Models\ServiceItemConsumption;

class LinkItemsToService extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-link';
    protected static \UnitEnum|string|null $navigationGroup = 'Inventory';
    protected static ?string $title = 'Link Items to Service';

    private array $serviceNameMap = [];

    protected string $view = 'filament.pages.link-items-to-service';

    public ?array $data = [];
    public ?int $serviceId = null;

    public function mount(): void
    {
        $this->form->fill();
        $this->serviceId = (int) ($this->data['service_id'] ?? 0);
    }

    public function form($form)
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->label('Service')
                    ->options(function () {
                        try {
                            $json = app(\App\Services\ServiceApi::class)->listServices();
                            $list = $json['data'] ?? $json;

                            return collect($list)->mapWithKeys(fn($row) => [
                                (string) $row['id'] => $row['name'],
                            ])->all();
                        } catch (\Throwable $e) {
                            report($e);
                            return [];
                        }
                    })
                    ->preload()
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        try {
                            $json = app(\App\Services\ServiceApi::class)->listServices($search);
                            $list = $json['data'] ?? $json;

                            return collect($list)->mapWithKeys(fn($row) => [
                                (string) $row['id'] => $row['name'],
                            ])->all();
                        } catch (\Throwable $e) {
                            report($e);
                            return [];
                        }
                    })
                    ->getOptionLabelUsing(function ($value) {
                        if (!$value)
                            return null;
                        try {
                            $svc = app(\App\Services\ServiceApi::class)->getService((int) $value);
                            return $svc['name'] ?? null;
                        } catch (\Throwable $e) {
                            report($e);
                            return null;
                        }
                    })
                    ->required(),

                Repeater::make('items')
                    ->label('Items per booking')
                    ->minItems(1)
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(fn() => Item::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('qty_per_service')
                            ->label('Qty per booking')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->columnSpanFull(),

            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn(): Builder =>
                ServiceItemConsumption::query()->with('item')
            )
            ->columns([
                TextColumn::make('service_name')
                    ->label('Service')
                    ->state(fn(ServiceItemConsumption $record) => $this->serviceName((int) $record->service_id))                 
                    ->sortable(
                        query: fn(Builder $query, string $direction) => $query->orderBy('service_id', $direction)
                    ),
                TextColumn::make('item.name')->label('Item')->searchable(),
                TextColumn::make('item.sku')->label('SKU'),
                TextColumn::make('qty_per_service')->label('Qty / booking')->sortable(),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->filters([
                SelectFilter::make('service')
                    ->label('Service')
                    // options from your Service API
                    ->options($this->serviceOptions())
                    // apply the filter to the query *only* when the user picks a value
                    ->query(function (Builder $query, array $data): Builder {
                        $id = $data['value'] ?? null;   // Filament stores the selected value here
                        return $query->when($id, fn($q) => $q->where('service_id', $id));
                    }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25, 50])
            ->striped()
            ->emptyStateHeading('No links found')
            ->emptyStateDescription('Use the Service filter above or add a new link, then Save.');
    }

    private function serviceNameMap(): array
    {
        if ($this->serviceNameMap) {
            return $this->serviceNameMap;
        }

        $map = [];
        $page = 1;

        try {
            do {
                $res = app(\App\Services\ServiceApi::class)->listServices('', $page);
                $rows = $res['data'] ?? [];
                foreach ($rows as $r) {
                    $map[(int) $r['id']] = $r['name'];
                }
                $last = (int) ($res['meta']['last_page'] ?? 1);
                $page++;
            } while ($page <= $last);
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->serviceNameMap = $map;
    }

    /** Get a friendly name for a single id. */
    private function serviceName(int $id): string
    {
        $map = $this->serviceNameMap();
        return $map[$id] ?? "#{$id}";
    }

    private function serviceOptions(): array
    {
        try {
            $json = app(\App\Services\ServiceApi::class)->listServices();
            $list = $json['data'] ?? $json;

            return collect($list)->mapWithKeys(fn($row) => [
                (string) $row['id'] => $row['name'],
            ])->all();
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }



    public function save(): void
    {
        $state = $this->form->getState();
        $serviceId = (int) ($state['service_id'] ?? 0);
        $rows = $state['items'] ?? [];

        if (!$serviceId) {
            Notification::make()->title('Please pick a service.')->danger()->send();
            return;
        }


        $keepItemIds = [];
        foreach ($rows as $r) {
            $itemId = (int) ($r['item_id'] ?? 0);
            $qty = (int) ($r['qty_per_service'] ?? 0);
            if (!$itemId || $qty < 1) {
                continue;
            }
            $keepItemIds[] = $itemId;

            ServiceItemConsumption::updateOrCreate(
                ['service_id' => $serviceId, 'item_id' => $itemId],
                ['qty_per_service' => $qty]
            );
        }


        ServiceItemConsumption::where('service_id', $serviceId)
            ->when(!empty($keepItemIds), fn($q) => $q->whereNotIn('item_id', $keepItemIds))
            ->delete();

        Notification::make()->title('Links saved.')->success()->send();


        $this->form->fill($this->form->getState());
    }
}
