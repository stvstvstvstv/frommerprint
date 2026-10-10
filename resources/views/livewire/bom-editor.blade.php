<div class="space-y-6">

    @if (session('status'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-semibold">
            BOM: {{ $bom->finishedMaterial->name }}
        </h2>
        <div class="mt-1 text-sm text-gray-500 space-x-4">
            <span>Code: <span class="font-mono">{{ $bom->finishedMaterial->code ?? '—' }}</span></span>
            <span> / SAGA ID: <span class="font-mono">{{ $bom->finishedMaterial->saga_id ?? '—' }}</span></span>
        </div>
        <div class="mt-1 text-sm text-gray-500 space-x-4">
            <span>Width: {{ $bom->finishedMaterial->width ?? '—' }} mm</span>
            <span>Length: {{ $bom->finishedMaterial->length ?? '—' }} m</span>
            <span>Setup: {{ $bom->setup_time_minutes }} min</span>
        </div>
    </div>

    {{-- Yield Rule --}}
    <div class="bg-white p-4 rounded shadow space-y-4">
        <h3 class="font-semibold">Yield Rule</h3>

        {{-- Jumbo --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Preferred Jumbo Roll</label>
                <select
                    wire:model.live="yieldRule.preferred_jumbo_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">— select —</option>
                    @foreach ($jumboOptions as $m)
                        <option value="{{ $m['id'] }}">
                            {{ $m['name'] }}
                            @if($m['width'])
                                ({{ $m['width'] }} mm
                                @if($m['length']) · {{ number_format($m['length']) }} m @endif
                                @if($m['gsm']) · {{ $m['gsm'] }} gsm @endif
                                )
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($costPreview['breakdown']['paper'] ?? null)
                @php $pb = $costPreview['breakdown']['paper']; @endphp
                <div class="text-xs text-gray-500 flex items-end pb-1 space-x-3">
                    <span>Rolls/shaft: <strong>{{ $pb['rolls_per_shaft'] }}</strong></span>
                    <span>Shafts: <strong>{{ $pb['shafts'] }}</strong></span>
                    <span>Net area: <strong>{{ $pb['net_area_m2'] }} m²</strong></span>
                    <span>Price/m²: <strong>{{ number_format($pb['price_per_m2'], 4) }}</strong></span>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Paper Waste %</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    wire:model.live="yieldRule.paper_waste_pct"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="1.00"
                >
            </div>
        </div>

        {{-- Ink --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Ink Material</label>
                <select
                    wire:model.live="yieldRule.ink_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">— none —</option>
                    @foreach ($inkOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Ink kg / m²</label>
                <input
                    type="number"
                    step="0.000001"
                    min="0"
                    wire:model.live="yieldRule.ink_kg_per_m2"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="e.g. 0.003"
                >
            </div>
        </div>

        {{-- Tube --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tube Material</label>
                <select
                    wire:model.live="yieldRule.tube_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">— none —</option>
                    @foreach ($tubeOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Packaging --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Foil Wrap Material</label>
                <select
                    wire:model.live="yieldRule.foil_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">— none —</option>
                    @foreach ($foilOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Rolls per Foil Wrap</label>
                <input
                    type="number"
                    step="1"
                    min="0"
                    wire:model.live="yieldRule.foil_per_roll"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="0 = no foil"
                >
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Box Material</label>
                <select
                    wire:model.live="yieldRule.box_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">— none —</option>
                    @foreach ($boxOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Rolls per Box</label>
                <input
                    type="number"
                    step="1"
                    min="0"
                    wire:model.live="yieldRule.rolls_per_box"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="e.g. 200"
                >
            </div>
        </div>
    </div>

    {{-- Non-geometric BOM items (machine, graphic, other) --}}
    @if (count($items) > 0)
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">Other Components</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs">
                        <th>Material</th>
                        <th>Role</th>
                        <th class="w-36">Qty / roll</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        <tr class="border-t">
                            <td class="py-1">{{ $item['material_name'] }}</td>
                            <td class="py-1 text-gray-500 text-xs">{{ $item['role'] }}</td>
                            <td class="py-1">
                                <input
                                    type="number"
                                    step="0.0001"
                                    wire:model.live="items.{{ $i }}.quantity"
                                    wire:change="recalculate"
                                    class="w-full border rounded px-2 py-1 text-sm"
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Cost Preview --}}
    <div class="bg-gray-50 p-4 rounded shadow space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Cost Preview</h3>
            <div class="flex items-center gap-2 text-sm">
                <label class="text-gray-500">Qty:</label>
                <input
                    type="number"
                    min="1"
                    wire:model.live="previewQuantity"
                    wire:change="recalculate"
                    class="w-20 border rounded px-2 py-1 text-sm"
                >
            </div>
        </div>

        {{-- Paper --}}
        @if ($costPreview['breakdown']['paper'] ?? null)
            @php $p = $costPreview['breakdown']['paper']; @endphp
            <div>
                <h4 class="font-semibold text-sm mb-1">Paper</h4>
                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr><td class="text-gray-500 w-40">Jumbo</td><td>{{ $p['jumbo'] }}</td></tr>
                        <tr><td class="text-gray-500">Rolls per shaft</td><td>{{ $p['rolls_per_shaft'] }}</td></tr>
                        <tr><td class="text-gray-500">Shafts</td><td>{{ $p['shafts'] }}</td></tr>
                        <tr><td class="text-gray-500">Gross area</td><td>{{ $p['gross_area_m2'] }} m²</td></tr>
                        <tr><td class="text-gray-500">Waste</td><td>{{ $p['waste_pct'] }} %</td></tr>
                        <tr><td class="text-gray-500">Net area</td><td>{{ $p['net_area_m2'] }} m²</td></tr>
                        <tr><td class="text-gray-500">Price / m²</td><td>{{ number_format($p['price_per_m2'], 6) }}</td></tr>
                        <tr class="font-semibold border-t"><td>Paper cost</td><td>{{ number_format($p['cost'], 4) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Ink --}}
        @if ($costPreview['breakdown']['ink'] ?? null)
            @php $ink = $costPreview['breakdown']['ink']; @endphp
            <div>
                <h4 class="font-semibold text-sm mb-1">Ink</h4>
                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr><td class="text-gray-500 w-40">Material</td><td>{{ $ink['material'] }}</td></tr>
                        <tr><td class="text-gray-500">Roll area</td><td>{{ $ink['roll_area_m2'] }} m²</td></tr>
                        <tr><td class="text-gray-500">kg / m²</td><td>{{ $ink['ink_kg_per_m2'] }}</td></tr>
                        <tr><td class="text-gray-500">kg / roll</td><td>{{ $ink['ink_kg_per_roll'] }}</td></tr>
                        <tr><td class="text-gray-500">Price / kg</td><td>{{ number_format($ink['unit_price'], 4) }}</td></tr>
                        <tr class="font-semibold border-t"><td>Ink cost</td><td>{{ number_format($ink['cost'], 4) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Tube --}}
        @if ($costPreview['breakdown']['tube'] ?? null)
            @php $tube = $costPreview['breakdown']['tube']; @endphp
            <div>
                <h4 class="font-semibold text-sm mb-1">Tube</h4>
                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr><td class="text-gray-500 w-40">Material</td><td>{{ $tube['material'] }}</td></tr>
                        <tr><td class="text-gray-500">Qty</td><td>{{ number_format($tube['quantity']) }}</td></tr>
                        <tr><td class="text-gray-500">Unit price</td><td>{{ number_format($tube['unit_price'], 4) }}</td></tr>
                        <tr class="font-semibold border-t"><td>Tube cost</td><td>{{ number_format($tube['cost'], 4) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Machine --}}
        @if (count($costPreview['breakdown']['machine'] ?? []) > 0)
            <div>
                <h4 class="font-semibold text-sm mb-1">Machine</h4>
                <table class="w-full text-xs border-collapse">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">Machine</th>
                            <th class="text-right">Time (h)</th>
                            <th class="text-right">Rate / h</th>
                            <th class="text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($costPreview['breakdown']['machine'] as $row)
                            @php
                                $totalMinutes = (int) round(($row['quantity'] ?? 0) * 60);
                                $hours = intdiv($totalMinutes, 60);
                                $mins  = $totalMinutes % 60;
                            @endphp
                            <tr class="border-t">
                                <td>{{ $row['material'] }}</td>
                                <td class="text-right">{{ number_format($row['quantity'], 2) }} ({{ $hours }}h {{ $mins }}m)</td>
                                <td class="text-right">{{ number_format($row['unit_price'], 2) }}</td>
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
                <h4 class="font-semibold text-sm mb-1">Other Components</h4>
                <table class="w-full text-xs border-collapse">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">Material</th>
                            <th class="text-left">Role</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit price</th>
                            <th class="text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($costPreview['breakdown']['components'] as $row)
                            <tr class="border-t">
                                <td>{{ $row['material'] }}</td>
                                <td>{{ $row['role'] }}</td>
                                <td class="text-right">{{ number_format($row['quantity'], 4) }}</td>
                                <td class="text-right">{{ number_format($row['unit_price'], 4) }}</td>
                                <td class="text-right">{{ number_format($row['cost'], 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Setup --}}
        @if (($costPreview['breakdown']['setup'] ?? 0) > 0)
            <div class="text-sm">
                Setup cost: <strong>{{ number_format($costPreview['breakdown']['setup'], 4) }}</strong>
            </div>
        @endif

        {{-- Packaging --}}
        @if (count($costPreview['breakdown']['packaging'] ?? []) > 0)
            <div>
                <h4 class="font-semibold text-sm mb-1">Packaging</h4>
                <table class="w-full text-xs border-collapse">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">Material</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit price</th>
                            <th class="text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($costPreview['breakdown']['packaging'] as $row)
                            <tr class="border-t">
                                <td>{{ $row['material'] }}</td>
                                <td class="text-right">{{ $row['quantity'] }}</td>
                                <td class="text-right">{{ number_format($row['unit_price'], 4) }}</td>
                                <td class="text-right">{{ number_format($row['cost'], 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Totals --}}
        <div class="border-t pt-2 space-y-1 text-sm">
            <div><strong>Unit cost:</strong> {{ number_format($costPreview['unit_cost'] ?? 0, 4) }}</div>
            <div><strong>Total ({{ $previewQuantity }} rolls):</strong> {{ number_format($costPreview['total_cost'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <div class="text-xs text-gray-500">Changes are not saved automatically.</div>
        <button
            wire:click="save"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
            Save BOM
        </button>
    </div>

</div>
