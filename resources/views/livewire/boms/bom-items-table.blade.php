<div class="space-y-6">

    {{-- Add new item --}}
    <div class="p-4 bg-gray-50 rounded shadow">
        <h3 class="font-semibold mb-3">Add Material</h3>

        <div class="grid grid-cols-6 gap-3">
            <select wire:model="newItem.component_material_id" class="col-span-2">
                <option value="">Select material…</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}">
                        {{ $material->name }}
                    </option>
                @endforeach
            </select>

            <input type="text" wire:model="newItem.role" placeholder="Role">
            <input type="number" step="0.0001" wire:model="newItem.quantity" placeholder="Qty">
            <input type="number" step="0.01" wire:model="newItem.waste_factor_pct" placeholder="Waste %">
            <input type="text" wire:model="newItem.notes" placeholder="Notes">
        </div>

        <button
            wire:click="addItem"
            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded"
        >
            Add
        </button>
    </div>

    {{-- Items table --}}
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Material</th>
                    <th class="p-2">Role</th>
                    <th class="p-2">Quantity</th>
                    <th class="p-2">Waste %</th>
                    <th class="p-2">Notes</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr class="border-t">
                        <td class="p-2">
                            {{ $item['component_material']['name'] ?? '—' }}
                        </td>

                        <td class="p-2">
                            <input wire:model.lazy="items.{{ $index }}.role"
                                   wire:blur="updateItem({{ $index }})">
                        </td>

                        <td class="p-2">
                            <input type="number" step="0.0001"
                                   wire:model.lazy="items.{{ $index }}.quantity"
                                   wire:blur="updateItem({{ $index }})">
                        </td>

                        <td class="p-2">
                            <input type="number" step="0.01"
                                   wire:model.lazy="items.{{ $index }}.waste_factor_pct"
                                   wire:blur="updateItem({{ $index }})">
                        </td>

                        <td class="p-2">
                            <input wire:model.lazy="items.{{ $index }}.notes"
                                   wire:blur="updateItem({{ $index }})">
                        </td>

                        <td class="p-2 text-right">
                            <button
                                wire:click="deleteItem({{ $item['id'] }})"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach

                @if(empty($items))
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">
                            No materials in this BOM yet.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>