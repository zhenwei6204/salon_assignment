<?php


namespace App\Filament\Resources\Refunds\Pages;

use App\Filament\Resources\Refunds\RefundResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRefund extends EditRecord
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Quick status change actions
            Actions\Action::make('approve')
                ->label('Approve Refund')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Refund approved')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\Action::make('complete')
                ->label('Mark Complete')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['approved', 'processing']))
                ->requiresConfirmation()
                ->modalHeading('Complete Refund')
                ->modalDescription('Mark this refund as completed. Ensure the refund has been processed.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Refund completed')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('delete_refunds')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Refund updated')
            ->body('The refund has been updated successfully.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-set timestamps based on status changes
        $originalStatus = $this->record->getOriginal('status');
        $newStatus = $data['status'];

        if ($originalStatus !== $newStatus) {
            switch ($newStatus) {
                case 'approved':
                    if (!$this->record->approved_at) {
                        $data['approved_at'] = now();
                        $data['approved_by'] = auth()->id();
                    }
                    break;
                case 'processing':
                    if (!$this->record->processed_at) {
                        $data['processed_at'] = now();
                    }
                    break;
                case 'completed':
                    if (!$this->record->completed_at) {
                        $data['completed_at'] = now();
                    }
                    break;
            }
        }

        return $data;
    }
}
                 