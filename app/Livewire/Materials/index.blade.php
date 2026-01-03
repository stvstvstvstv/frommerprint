    <div class="p-6 space-y-6">

        <h1 class="text-xl font-semibold">
            {{ __('materials.title') }}
        </h1>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <div class="bg-white p-4 rounded shadow space-y-4">

            <div class="grid grid-cols-4 gap-4">

                <input
                    type="text"
                    wire:model.defer="code"
                    placeholder="{{ __('materials.code') }}"
                    class="border rounded px-2 py-1"
                >

                <input
                    type="text"
                    wire:model.defer="name"
                    placeholder="{{ __('materials.name') }}"
                    class="border rounded px-2 py-1"
                >

                <select
                    wire:model.defer="material_type"
                    class="border rounded px-2 py-1"
                >
                    <option value="raw">{{ __('materials.types.raw') }}</option>
                    <option value="finished">{{ __('materials.types.finished') }}</option>
                    <option value="packaging">{{ __('materials.types.packaging') }}</option>
                </select>

                <select
                    wire:model.defer="unit_id"
                    class="border rounded px-2 py-1"
                >
                    <option value="">{{ __('common.select') }}</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">
                            {{ $unit->code }}
                        </option>
                    @endforeach
                </select>

            </div>

            <label class="inline-flex items-center">
                <input type="checkbox" wire:model.defer="active" class="mr-2">
                {{ __('common.active') }}
            </label>

            <div>
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="bg-blue-600 text-white px-4 py-2 rounded"
                >
                    {{ $materialId ? __('common.update') : __('common.save') }}
                </button>

                @if ($materialId)
                    <button
                        wire:click="resetForm"
                        class="ml-2 bg-gray-400 text-white px-4 py-2 rounded"
                    >
                        {{ __('common.cancel') }}
                    </button>
                @endif
            </div>

        </div>

        {{-- Materials table --}}
        <div class="bg-white rounded shadow">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 text-left">{{ __('materials.code') }}</th>
                        <th class="p-2 text-left">{{ __('materials.name') }}</th>
                        <th class="p-2 text-left">{{ __('materials.type') }}</th>
                        <th class="p-2 text-left">{{ __('materials.unit') }}</th>
                        <th class="p-2 text-center">{{ __('common.active') }}</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $material)
                        <tr class="border-t">
                            <td class="p-2">{{ $material->code }}</td>
                            <td class="p-2">{{ $material->name }}</td>
                            <td class="p-2">
                                {{ __('materials.types.' . $material->material_type) }}
                            </td>
                            <td class="p-2">{{ $material->unit->code }}</td>
                            <td class="p-2 text-center">
                                {{ $material->active ? '✓' : '' }}
                            </td>
                            <td class="p-2 text-right">
                                <button
                                    wire:click="edit({{ $material->id }})"
                                    class="text-blue-600"
                                >
                                    {{ __('common.edit') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                {{ __('materials.none') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>