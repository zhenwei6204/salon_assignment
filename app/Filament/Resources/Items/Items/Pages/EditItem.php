<?php

namespace App\Filament\Resources\Items\Items\Pages;

use App\Filament\Resources\Items\Items\ItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use App\Models\StockMovement;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;


    protected function getRedirectUrl(): string
    {
        // same like create item
        return static::getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addStock')
                ->label('Add stock')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->form([
                    TextInput::make('quantity')->numeric()->integer()->minValue(1)->required(),
                    Textarea::make('reason')->rows(2),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $record->increment('stock', (int) $data['quantity']);

                    StockMovement::create([
                        'item_id'  => $record->id,
                        'type'     => 'in',
                        'quantity' => (int) $data['quantity'],
                        'reason'   => $data['reason'] ?? null,
                        'user_id'  => auth()->id(),
                    ]);

                    Notification::make()->title('Stock added')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            Action::make('deductStock')
                ->label('Deduct stock')
                ->icon('heroicon-o-minus')
                ->color('danger')
                ->form([
                    TextInput::make('quantity')->numeric()->integer()->minValue(1)->required(),
                    Textarea::make('reason')->rows(2)->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $qty = (int) $data['quantity'];

                    if ($qty > $record->stock) {
                        throw ValidationException::withMessages([
                            'quantity' => "Cannot deduct more than current stock ({$record->stock}).",
                        ]);
                    }

                    $record->decrement('stock', $qty);

                    StockMovement::create([
                        'item_id'  => $record->id,
                        'type'     => 'out',
                        'quantity' => $qty,
                        'reason'   => $data['reason'],
                        'user_id'  => auth()->id(),
                    ]);

                    Notification::make()->title('Stock deducted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            DeleteAction::make(),
        ];
    }
}
