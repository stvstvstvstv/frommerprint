
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
                    Cancel
                </button>

                <button
                    wire:click="deleteConfirmed"
                    class="px-4 py-2 rounded bg-red-600 text-white"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
@endif

    @if($showForm)
        <div class="bg-white p-4 rounded shadow mb-6">
            <h3 class="font-semibold mb-4">
                {{ $editingId ? 'Edit Material' : 'Add Material' }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input
                    type="text"
                    wire:model.live="code"
                    placeholder="Code"
                    class="rounded border-gray-300"
                />

                <input
                    type="text"
                    wire:model.live="name"
                    placeholder="Name"
                    class="rounded border-gray-300"
                />

                <select wire:model.live="material_type" class="rounded border-gray-300">
                    <option value="">Type</option>
                    <option value="raw">Raw</option>
                    <option value="finished">Finished</option>
                    <option value="packaging">Packaging</option>
                </select>

                <select wire:model.live="unit_id" class="rounded border-gray-300">
                    <option value="">Unit</option>
                    @foreach(\App\Models\Unit::orderBy('code')->get() as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                    @endforeach
                </select>

                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model.live="active">
                    Active
                </label>
            </div>

            <div class="mt-4 flex gap-2">
                <button
                    type="button"
                    wire:click="save"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    {{ $editingId ? __('common.update') : __('common.save') }}
                </button>

                <button
                    type="button"
                    wire:click="resetForm"
                    class="bg-gray-500 text-white px-4 py-2 rounded"
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
                <option value="raw">{{ __('materials.types.raw') }}</option>
                <option value="finished">{{ __('materials.types.finished') }}</option>
                <option value="packaging">{{ __('materials.types.packaging') }}</option>
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
    <div class="w-2/3">
        <table class="min-w-full border border-gray-200 rounded">
            <thead class="bg-gray-100 text-sm text-gray-700">
                <tr>
                    <th class="px-3 py-2 w-40 text-left">{{ __('materials.fields.code') }}</th>
                    <th class="px-3 py-2 text-left">{{ __('materials.fields.name') }}</th>
                    <th class="px-3 py-2 w-28 text-left">{{ __('materials.fields.type') }}</th>
                    <th class="px-3 py-2 w-20 text-left">{{ __('materials.fields.unit') }}</th>
                    <th class="px-3 py-2 w-20text-center">{{ __('materials.fields.active') }}</th>
                    <th class="px-3 py-2 w-24 text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($materials as $material)
                    <tr
                        wire:key="material-{{ $material->id }}"
                        wire:click="selectMaterial({{ $material->id }})"
                        class="cursor-pointer hover:bg-gray-50
                            {{ $selectedMaterialId === $material->id ? 'bg-gray-100' : '' }}"
                    >
                        <td class="px-3 py-2">{{ $material->code }}</td>
                        <td class="px-3 py-2">{{ $material->name }}</td>
                        <td class="px-3 py-2">{{ $material->material_type }}</td>
                        <td class="px-3 py-2">
                            {{ $material->unit?->code ?? '—' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if ($material->active)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="inline-flex items-center gap-1">
                                {{-- Edit --}}
                                <button
                                    wire:click="edit({{ $material->id }})"
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
                                    wire:click="confirmDelete({{ $material->id }})"
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
    <div class="w-1/3">
        @if ($selectedMaterialId)
            <div class="bg-white border rounded p-4">
                <h3 class="font-semibold mb-4">
                    Pricing
                </h3>

                {{-- READ-ONLY --}}
                @if (! $editMode)
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Price</dt>
                            <dd>
                                {{ $price !== null ? number_format($price, 2) : '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between">
                            <dt class="text-gray-600">Currency</dt>
                            <dd>{{ $currency }}</dd>
                        </div>

                        <div class="flex justify-between">
                            <dt class="text-gray-600">Pricing</dt>
                            <dd>
                                per {{ $pricingPer }}
                                {{ optional($units->firstWhere('id', $pricingUnitId))->code ?? '—' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 text-right">
                        <button
                            wire:click="$set('editMode', true)"
                            class="text-blue-600"
                        >
                            Edit
                        </button>
                    </div>
                @endif

                {{-- EDIT MODE --}}
                @if ($editMode)
                    <div class="space-y-3 text-sm">
                        <input type="number" step="0.01" wire:model.defer="price" class="w-full border rounded px-2 py-1" />

                        <select wire:model.defer="currency" class="w-full border rounded px-2 py-1">
                            <option value="EUR">EUR</option>
                            <option value="RON">RON</option>
                        </select>

                        <div class="flex gap-2">
                            <input type="number" wire:model.defer="pricingPer" class="w-1/2 border rounded px-2 py-1" />

                            <select wire:model.defer="pricingUnitId" class="w-1/2 border rounded px-2 py-1">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button wire:click="cancelPricingEdit" class="text-gray-600">
                                Cancel
                            </button>
                            <button wire:click="savePricing" class="text-blue-600 font-semibold">
                                Save
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="text-gray-400 italic">
                Select a material to view pricing
            </div>
        @endif
    </div>
</div>

<div class="mt-4">
    {{ $materials->links() }}
</div>

