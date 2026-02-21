<x-app-layout>

<div class="max-w-7xl mx-auto p-6 space-y-6">

    <div>
        <h1 class="text-xl font-semibold">
            BOM Structure – {{ $bom->finishedMaterial->name ?? '—' }}
        </h1>

        <div class="text-sm text-gray-500">
            Manage materials and quantities (no costing)
        </div>
    </div>

    <livewire:boms.bom-items-table
        :bom="$bom"
        wire:key="bom-items-{{ $bom->id }}"
    />

</div>

</x-app-layout>