{{-- resources/views/filament/pages/link-items-to-service.blade.php --}}
<x-filament::page>
    {{ $this->form }}

    <div class="mt-4">
        <x-filament::button wire:click="save">
            Save
        </x-filament::button>
    </div>
</x-filament::page>
