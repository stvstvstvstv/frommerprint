
<div class="overflow-x-auto p-4 bg-white rounded shadow">

@if ($deleteId)
    <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
        <div class="bg-white rounded shadow p-6 w-full max-w-sm">
            <h3 class="font-semibold mb-3">{{ __('materials.confirm_delete_title') }}</h3>
            <p class="text-sm text-gray-600 mb-4">
                {{ __('materials.confirm_delete_body') }}
            </p>

            <div class="flex justify-end gap-2">
                <button
                    wire:click="$set('deleteId', null)"
                    class="px-4 py-2 rounded border border-gray-300 text-gray-700"
                >
                    {{ __('materials.cancel') }}
                </button>

                <button
                    wire:click="deleteConfirmed"
                    class="px-4 py-2 rounded bg-red-600 text-white"
                >
                    {{ __('materials.delete') }}
                </button>
            </div>
        </div>
    </div>
@endif

    @if($showForm)
        <div class="bg-white p-4 rounded shadow mb-6">
            <h3 class="font-semibold mb-4">
                {{ $editingId ? __('materials.edit') : __('materials.add') }}
            </h3>

            {{-- Row 1: Code, SAGA ID, Name --}}
            <div class="grid grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.code') }}</label>
                    <input
                        type="text"
                        wire:model.live="code"
                        class="w-full font-mono rounded border-gray-300 text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.sagaid') }}</label>
                    <input
                        type="text"
                        wire:model.live="saga_id"
                        class="w-full font-mono rounded border-gray-300 text-sm"
                    />
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.name') }}</label>
                    <input
                        type="text"
                        wire:model.live="name"
                        class="w-full rounded border-gray-300 text-sm"
                    />
                </div>
            </div>

            {{-- Row 2: Type, Unit, Active --}}
            <div class="grid grid-cols-12 gap-3 mb-3">
                <div class="col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.type') }}</label>
                    <select wire:model.live="material_type" class="w-full rounded border-gray-300 text-sm">
                        <option value="">—</option>
                        <option value="finished">{{ __('materials.types.finished') }}</option>
                        <option value="jumbo">{{ __('materials.types.jumbo') }}</option>
                        <option value="paper">{{ __('materials.types.paper') }}</option>
                        <option value="tube">{{ __('materials.types.tube') }}</option>
                        <option value="ink">{{ __('materials.types.ink') }}</option>
                        <option value="foil">{{ __('materials.types.foil') }}</option>
                        <option value="box">{{ __('materials.types.box') }}</option>
                        <option value="glue">{{ __('materials.types.glue') }}</option>
                        <option value="label">{{ __('materials.types.label') }}</option>
                        <option value="machine">{{ __('materials.types.machine') }}</option>
                        <option value="other">{{ __('materials.types.other') }}</option>
                    </select>
                </div>
                <div class="col-span-3">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.unit') }}</label>
                    <select wire:model.live="unit_id" class="w-full rounded border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach(\App\Models\Unit::orderBy('code')->get() as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2 flex items-end pb-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="active">
                        {{ __('materials.fields.active') }}
                    </label>
                </div>
            </div>

            {{-- Row 3: Dimensions --}}
            <div class="grid grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.width') }}</label>
                    <input type="number" step="0.01" wire:model.live="width" class="w-full rounded border-gray-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.length') }}</label>
                    <input type="number" step="0.01" wire:model.live="length" class="w-full rounded border-gray-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.gsm') }}</label>
                    <input type="number" step="0.01" wire:model.live="gsm" class="w-full rounded border-gray-300 text-sm" />
                </div>
                @if ($material_type === 'tube')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.diameter') }}</label>
                    <input type="number" step="0.01" wire:model.live="diameter" class="w-full rounded border-gray-300 text-sm" />
                </div>
                @endif
            </div>

            {{-- Row 3: Pricing --}}
            <div class="grid grid-cols-12 gap-3 mb-4">
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.currency') }}</label>
                    <select wire:model.live="currencyId" class="w-full rounded border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.price') }}</label>
                    <input type="number" step="0.01" wire:model.live="price" class="w-full rounded border-gray-300 text-sm" />
                </div>
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.pricing_per') }}</label>
                    <input type="number" wire:model.live="pricingPer" class="w-full rounded border-gray-300 text-sm" />
                </div>
                <div class="col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">{{ __('materials.fields.pricing_unit') }}</label>
                    <select wire:model.live="pricingUnitId" class="w-full rounded border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="button"
                    wire:click="save"
                    class="bg-blue-600 text-white px-4 py-2 rounded text-sm"
                >
                    {{ $editingId ? __('common.update') : __('common.save') }}
                </button>
                <button
                    type="button"
                    wire:click="resetForm"
                    class="bg-gray-500 text-white px-4 py-2 rounded text-sm"
                >
                    {{ __('common.cancel') }}
                </button>
            </div>

            @error('*')
                <div class="mt-3 text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>

    @endif

    <div class="flex items-center justify-between mb-3 gap-4">
        {{-- Filters --}}
        <div class="flex items-center gap-3 flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('materials.search_placeholder') }}"
                class="w-full max-w-sm rounded border-gray-300 text-sm"
            />

            <select
                wire:model.live="type"
                class="rounded border-gray-300 text-sm"
            >
                <option value="">{{ __('common.all') }}</option>
                <option value="finished">{{ __('materials.types.finished') }}</option>
                <option value="jumbo">{{ __('materials.types.jumbo') }}</option>
                <option value="paper">{{ __('materials.types.paper') }}</option>
                <option value="tube">{{ __('materials.types.tube') }}</option>
                <option value="ink">{{ __('materials.types.ink') }}</option>
                <option value="foil">{{ __('materials.types.foil') }}</option>
                <option value="box">{{ __('materials.types.box') }}</option>
                <option value="glue">{{ __('materials.types.glue') }}</option>
                <option value="label">{{ __('materials.types.label') }}</option>                    
                <option value="machine">{{ __('materials.types.machine') }}</option>
                <option value="other">{{ __('materials.types.other') }}</option>
            </select>
        </div>

        {{-- Add button --}}
        <button
            wire:click="create"
            class="text-blue-600 hover:underline text-sm font-medium whitespace-nowrap"
        >
            {{ __('materials.add') }}
        </button>
    </div>

<div class="flex gap-6">
    {{-- LEFT: table --}}
    <div class="w-3/4">
        <table class="w-full border border-gray-200 rounded text-xs">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-2 py-1 w-40 text-left">{{ __('materials.fields.code') }}</th>
                    <th class="px-2 py-1 text-left">{{ __('materials.fields.dimensions') }}</th>
                    <th class="px-2 py-1 text-left">{{ __('materials.printed') }}</th>
                    <th class="px-2 py-1 text-left">{{ __('materials.fields.name') }}</th>
                    <th class="px-2 py-1 w-20 text-left">{{ __('materials.fields.type') }}</th>
                    <th class="px-2 py-1 w-14 text-left">{{ __('materials.fields.unit') }}</th>
                    <th class="px-2 py-1 w-20text-center">{{ __('materials.fields.active') }}</th>
                    <th class="px-2 py-1 w-24 text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($materials as $material)

                @php($isSelected = $selectedMaterialId === $material->id)
                    <tr
                        wire:key="material-{{ $material->id }}"
                        wire:click="selectMaterial({{ $material->id }})"
                        @class([
                            'cursor-pointer transition-colors hover:bg-gray-50',
                            'bg-blue-50 font-medium' => $isSelected,
                        ])
                    >
                        @php($isSelected = $selectedMaterialId === $material->id)

                        <td class="px-2 py-1 border-l-4 {{ $isSelected ? 'border-blue-500' : 'border-transparent' }}"><span class="font-mono">{{ $material->code }}</span></td>
                        <td class="px-2 py-1">{{ $material->dimensions }}</td>
                        <td class="px-2 py-1">{{ $material->printed }}</td>
                        <td class="px-2 py-1 {{ $isSelected ? 'bg-blue-50' : '' }}">{{ $material->name }}</td>
                        <td class="px-2 py-1">{{ __('materials.types.' . $material->material_type) }}</td>
                        <td class="px-2 py-1">{{ $material->unit?->code ?? '—' }}</td>
                        <td class="px-2 py-1 text-center">
                            @if ($material->active)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="inline-flex items-center gap-1">
                                {{-- BOM link for finished materials --}}
                                @if ($material->material_type === 'finished')
                                    <a
                                        href="/bom/{{ $material->id }}"
                                        wire:navigate.stop
                                        class="p-1.5 rounded text-blue-500 hover:text-blue-800 hover:bg-blue-50"
                                        title="Edit BOM"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" stroke-linejoin="miter">
                                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                                            <path d="M9 12h6M9 16h4"/>
                                        </svg>
                                    </a>
                                @endif

                                {{-- Edit --}}
                                <button
                                    wire:click.stop="edit({{ $material->id }})"
                                    class="p-1.5 rounded text-gray-500 hover:text-gray-800 hover:bg-gray-100"
                                    title="Edit"
                                >
                                    <!-- Edit (sharp pencil) -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.75"
                                        stroke-linecap="square"
                                        stroke-linejoin="miter">
                                        <path d="M4 20h4l10-10-4-4L4 16v4z"/>
                                        <path d="M14 6l4 4"/>
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <button
                                    wire:click.stop="confirmDelete({{ $material->id }})"
                                    class="p-1.5 rounded text-gray-500 hover:text-red-700 hover:bg-gray-100"
                                    title="Delete"
                                >
                                    <!-- Delete (minimal trash) -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.75"
                                        stroke-linecap="square"
                                        stroke-linejoin="miter">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M6 6l1 14h10l1-14"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                            {{ __('materials.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- RIGHT: pricing panel --}}
    <div class="w-1/4">
        @if ($selectedMaterialId)
            <div class="bg-white border rounded p-4">
                <h3 class="font-semibold mb-4">
                    {{ $material_name }} - {{ $material_type === 'finished' ? __('materials.price') : __('materials.cost') }}
                </h3>

                <div class="flex items-center gap-8 text-sm tabular-nums">
                    {{-- Currency --}}
                    <div class="flex items-baseline gap-2">
                        <div class="text-gray-500">Currency</div>
                        <div class="font-medium">
                            {{ optional($currencies->firstWhere('id', $currencyId))->code ?? '—' }}
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="flex items-baseline gap-2">
                        <div class="text-gray-500">Price</div>
                        <div class="font-medium">
                            {{ $price !== null ? number_format($price, 2) : '—' }}
                        </div>
                    </div>

                    {{-- Per --}}
                    <div class="flex items-baseline gap-2 whitespace-nowrap">
                        <div class="text-gray-500">Per</div>
                        <div class="font-medium">
                            {{ $pricingPer }}
                            {{ optional($units->firstWhere('id', $pricingUnitId))->code ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-gray-400 italic">
                {{ __('materials.selecttoview') }}
            </div>
        @endif
    </div>
</div>

<div class="mt-4">
    {{ $materials->links() }}
</div>

<!--  Handle the save confirm dialog  --!>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirm-material-switch', ({ newId }) => {
            if (confirm('You have unsaved changes. Save them first?')) {
                Livewire.find(@this.__instance.id).call('save');
                Livewire.find(@this.__instance.id).call('selectMaterialNow', newId);
            } else if (confirm('Discard changes and continue?')) {
                Livewire.find(@this.__instance.id).call('resetForm');
                Livewire.find(@this.__instance.id).call('selectMaterialNow', newId);
            }
            // else: do nothing (cancel)
        });
    });
</script>