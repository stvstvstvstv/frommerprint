<div class="overflow-x-auto">

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
                wire:click="save"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                {{ $editingId ? 'Update' : 'Save' }}
            </button>

            @if($editingId)
                <button
                    wire:click="resetForm"
                    class="bg-gray-400 text-white px-4 py-2 rounded"
                >
                    Cancel
                </button>
            @endif
        </div>

        @error('*')
            <div class="mt-3 text-sm text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        {{-- Search --}}
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search code or name…"
                class="w-full rounded border-gray-300"
            />
        </div>

        {{-- Type filter --}}
        <div class="w-full sm:w-48">
            <select
                wire:model.live="type"
                class="w-full rounded border-gray-300"
            >
                <option value="">All types</option>
                <option value="raw">Raw</option>
                <option value="finished">Finished</option>
                <option value="packaging">Packaging</option>
            </select>
        </div>
    </div>

    <table class="min-w-full border border-gray-200 rounded">
        <thead class="bg-gray-100 text-sm text-gray-700">
            <tr>
                <th class="px-3 py-2 text-left">Code</th>
                <th class="px-3 py-2 text-left">Name</th>
                <th class="px-3 py-2 text-left">Type</th>
                <th class="px-3 py-2 text-left">Unit</th>
                <th class="px-3 py-2 text-center">Active</th>
            </tr>
        </thead>

        <tbody class="divide-y">
            @forelse ($materials as $material)
                <tr class="hover:bg-gray-50">
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
                    <td class="px-3 py-2 text-right space-x-2">
                        <button
                            wire:click="edit({{ $material->id }})"
                            class="text-blue-600"
                        >
                            Edit
                        </button>

                        <button
                            wire:click="delete({{ $material->id }})"
                            class="text-red-600"
                            onclick="return confirm('Delete this material?')"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                        No materials found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $materials->links() }}
    </div>
</div>
