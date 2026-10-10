<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">
            Edit Draft Order <span class="text-gray-400 font-normal">#{{ $order->id }}</span>
        </h2>
        <a href="{{ route('production-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            &larr; Back to orders
        </a>
    </div>

    @if ($warning)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm px-4 py-3 rounded">
            {{ $warning }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ---------------------------------------------------------------- --}}
        {{-- LEFT: Form                                                        --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="space-y-4">

            {{-- Product --}}
            <div class="bg-white p-4 rounded shadow space-y-4">
                <h3 class="font-semibold text-gray-700">Product</h3>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Finished Material</label>

                    @if ($finishedMaterialId)
                        <div class="flex items-center gap-2 border rounded px-2 py-1 bg-gray-50">
                            <span class="text-sm flex-1 truncate">{{ $selectedFinishedName }}</span>
                            @if ($selectedFinishedCode)
                                <span class="text-xs text-gray-400 shrink-0">{{ $selectedFinishedCode }}</span>
                            @endif
                            <button wire:click="clearFinishedMaterial"
                                    class="text-gray-400 hover:text-red-500 text-xs leading-none"
                                    title="Clear selection">&times;</button>
                        </div>
                        @if ($selectedFinishedDims)
                            <div class="text-xs text-gray-400 mt-1 flex gap-3">
                                <span>{{ $selectedFinishedDims['width'] ?? '—' }} mm</span>
                                <span>× {{ $selectedFinishedDims['length'] ?? '—' }} m</span>
                                <span>{{ $selectedFinishedDims['gsm'] ?? '—' }} gsm</span>
                            </div>
                        @endif
                    @else
                        <div class="relative z-20">
                            <input
                                type="text"
                                wire:model.live.debounce.200ms="finishedSearch"
                                wire:focus="openFinishedDropdown"
                                wire:keydown.escape="closeFinishedDropdown"
                                placeholder="Click or type to search…"
                                autocomplete="off"
                                class="w-full border rounded px-2 py-1 text-sm"
                            >

                            @if ($showFinishedDropdown)
                                <div class="fixed inset-0 z-10" wire:click="closeFinishedDropdown"></div>
                                <div class="absolute z-20 w-full mt-1 bg-white border rounded shadow-lg max-h-64 overflow-y-auto">
                                    @forelse ($finishedSearchResults as $m)
                                        <button type="button"
                                                wire:key="fs-{{ $m['id'] }}"
                                                wire:click="selectFinishedMaterial({{ $m['id'] }})"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 border-b border-gray-50 last:border-0">
                                            <div class="font-medium truncate">{{ $m['name'] }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ $m['code'] }}
                                                @if($m['saga_id']) &middot; {{ $m['saga_id'] }} @endif
                                                @if($m['width']) &middot; {{ $m['width'] }}mm &times; {{ $m['length'] }}m @endif
                                            </div>
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-sm text-gray-400 italic">No results.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif

                    @error('finishedMaterialId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Editable fields --}}
            <div class="bg-white p-4 rounded shadow space-y-4">
                <h3 class="font-semibold text-gray-700">Materials &amp; Quantity</h3>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Client</label>
                    <input type="text" wire:model.live="client"
                           class="w-full border rounded px-2 py-1 text-sm" placeholder="Customer name">
                    @error('client') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Planned Quantity (rolls)</label>
                    <input type="number" min="1" wire:model.live="plannedRolls"
                           class="w-full border rounded px-2 py-1 text-sm">
                    @error('plannedRolls') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jumbo Roll</label>
                    <select wire:model.live="jumboMaterialId"
                            class="w-full border rounded px-2 py-1 text-sm">
                        <option value="">— select jumbo —</option>
                        @foreach ($jumboOptions as $m)
                            <option value="{{ $m['id'] }}">
                                {{ $m['name'] }}{{ $m['width'] ? ' (' . $m['width'] . ' mm' . ($m['gsm'] ? ', ' . $m['gsm'] . ' gsm' : '') . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('jumboMaterialId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ink kg / m²</label>
                        <input type="number" step="0.000001" min="0"
                               wire:model.live="overrideInkKgPerM2"
                               placeholder="from BOM"
                               class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Rolls per Foil Wrap</label>
                        <input type="number" step="1" min="0"
                               wire:model.live="overrideFoilPerRoll"
                               class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Rolls per Box</label>
                        <input type="number" step="1" min="0"
                               wire:model.live="overrideRollsPerBox"
                               class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button wire:click="save"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Save Changes
                </button>
                <button wire:click="confirmOrder"
                        wire:confirm="Move order #{{ $order->id }} to In Production? This will freeze the cost."
                        class="px-6 py-2 bg-blue-700 text-white rounded hover:bg-blue-800 text-sm font-medium">
                    → In Production
                </button>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- RIGHT: Cost Preview                                               --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="bg-gray-50 p-4 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-700">Cost Preview
                @if($plannedRolls > 0)
                    <span class="font-normal text-gray-400 text-sm">— {{ number_format($plannedRolls) }} rolls</span>
                @endif
            </h3>

            @if (empty($costPreview))
                <p class="text-sm text-gray-400 italic">Select a jumbo to see cost estimate.</p>
            @else

                {{-- Paper --}}
                @if ($costPreview['breakdown']['paper'] ?? null)
                    @php $p = $costPreview['breakdown']['paper']; @endphp
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Paper</h4>
                        <table class="w-full text-xs border-collapse">
                            <tbody>
                                <tr><td class="text-gray-500 w-40">Jumbo</td><td>{{ $p['jumbo'] }}</td></tr>
                                <tr><td class="text-gray-500">Rolls / shaft</td><td>{{ $p['rolls_per_shaft'] }}</td></tr>
                                <tr><td class="text-gray-500">Shafts</td><td>{{ $p['shafts'] }}</td></tr>
                                <tr><td class="text-gray-500">Net area</td><td>{{ $p['net_area_m2'] }} m²</td></tr>
                                <tr><td class="text-gray-500">Price / m²</td><td>{{ number_format($p['price_per_m2'], 4) }}</td></tr>
                                <tr class="font-semibold border-t"><td>Paper cost</td><td>{{ number_format($p['cost'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Ink --}}
                @if ($costPreview['breakdown']['ink'] ?? null)
                    @php $ink = $costPreview['breakdown']['ink']; @endphp
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Ink</h4>
                        <table class="w-full text-xs border-collapse">
                            <tbody>
                                <tr><td class="text-gray-500 w-40">Material</td><td>{{ $ink['material'] }}</td></tr>
                                <tr><td class="text-gray-500">kg / roll</td><td>{{ $ink['ink_kg_per_roll'] }}</td></tr>
                                <tr class="font-semibold border-t"><td>Ink cost</td><td>{{ number_format($ink['cost'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Packaging --}}
                @if (count($costPreview['breakdown']['packaging'] ?? []) > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Packaging</h4>
                        <table class="w-full text-xs border-collapse">
                            <thead class="text-gray-500">
                                <tr><th class="text-left">Material</th><th class="text-right">Qty</th><th class="text-right">Cost</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($costPreview['breakdown']['packaging'] as $row)
                                    <tr class="border-t">
                                        <td>{{ $row['material'] }}</td>
                                        <td class="text-right">{{ $row['quantity'] }}</td>
                                        <td class="text-right">{{ number_format($row['cost'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Other components --}}
                @if (count($costPreview['breakdown']['components'] ?? []) > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Other Components</h4>
                        <table class="w-full text-xs border-collapse">
                            <tbody>
                                @foreach ($costPreview['breakdown']['components'] as $row)
                                    <tr class="border-t">
                                        <td>{{ $row['material'] }}</td>
                                        <td class="text-right">{{ number_format($row['cost'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Totals --}}
                <div class="border-t pt-3 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Unit cost</span>
                        <strong>{{ number_format($costPreview['unit_cost'], 4) }}</strong>
                    </div>
                    <div class="flex justify-between text-base font-semibold">
                        <span>Total ({{ number_format($plannedRolls) }} rolls)</span>
                        <span>{{ number_format($costPreview['total_cost'], 2) }}</span>
                    </div>
                </div>

            @endif
        </div>

    </div>
</div>
