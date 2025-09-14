{{-- resources/views/filament/pages/link-items-to-service.blade.php --}}
<x-filament-panels::page>
    {{ $this->form }}

    {{-- simple divider instead of <x-filament::hr /> --}}
    <div class="my-4 h-px bg-gray-200 dark:bg-gray-700"></div>

    {{-- optional wrapper; you can also drop the section and keep only {{ $this->table }} --}}
    <x-filament::section
        heading="Currently linked items"
        description="Existing item–service links for the selected service."
    >
        {{ $this->table }}
    </x-filament::section>

    <div class="mt-4">
        <x-filament::button wire:click="save">Save</x-filament::button>
    </div>
</x-filament-panels::page>
