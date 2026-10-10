<div class="space-y-6">

    @if (session('status'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-semibold">
            {{ __('bom.title', ['name' => $bom->finishedMaterial->name]) }}
        </h2>
        <div class="mt-1 text-sm text-gray-500 space-x-4">
            <span>{{ __('bom.code') }}: <span class="font-mono">{{ $bom->finishedMaterial->code ?? '—' }}</span></span>
            <span> / {{ __('bom.saga_id') }}: <span class="font-mono">{{ $bom->finishedMaterial->saga_id ?? '—' }}</span></span>
        </div>
        <div class="mt-1 text-sm text-gray-500 space-x-4">
            <span>{{ __('bom.width') }}: {{ $bom->finishedMaterial->width ?? '—' }} mm</span>
            <span>{{ __('bom.length') }}: {{ $bom->finishedMaterial->length ?? '—' }} m</span>
            <span>{{ __('bom.setup') }}: {{ $bom->setup_time_minutes }} min</span>
        </div>
    </div>

    {{-- Yield Rule --}}
    <div class="bg-white p-4 rounded shadow space-y-4">
        <h3 class="font-semibold">{{ __('bom.yield_rule') }}</h3>

        {{-- Jumbo --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.preferred_jumbo') }}</label>
                <select
                    wire:model.live="yieldRule.preferred_jumbo_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">{{ __('bom.select') }}</option>
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

            @if ($costPreview['breakdown']['paper']['error'] ?? null)
                <div class="flex items-end pb-1">
                    <x-paper-cost-problem :paper="$costPreview['breakdown']['paper']" />
                </div>
            @elseif ($costPreview['breakdown']['paper'] ?? null)
                @php $pb = $costPreview['breakdown']['paper']; @endphp
                <div class="text-xs text-gray-500 flex items-end pb-1 space-x-3">
                    <span>{{ __('bom.rolls_per_shaft') }}: <strong>{{ $pb['rolls_per_shaft'] }}</strong></span>
                    <span>{{ __('bom.shafts') }}: <strong>{{ $pb['shafts'] }}</strong></span>
                    <span>{{ __('bom.net_area') }}: <strong>{{ $pb['net_area_m2'] }} m²</strong></span>
                    <span>{{ __('bom.price_per_m2') }}: <strong>{{ ($pb['price_per_m2'] === null ? '—' : number_format($pb['price_per_m2'], 4)) }}</strong></span>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.paper_waste_pct') }}</label>
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
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.ink_material') }}</label>
                <select
                    wire:model.live="yieldRule.ink_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">{{ __('bom.none') }}</option>
                    @foreach ($inkOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.ink_kg_m2') }}</label>
                <input
                    type="number"
                    step="0.000001"
                    min="0"
                    wire:model.live="yieldRule.ink_kg_per_m2"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="{{ __('bom.example', ['value' => '0.003']) }}"
                >
            </div>
        </div>

        {{-- Tube --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.tube_material') }}</label>
                <select
                    wire:model.live="yieldRule.tube_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">{{ __('bom.none') }}</option>
                    @foreach ($tubeOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Packaging --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.foil_material') }}</label>
                <select
                    wire:model.live="yieldRule.foil_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">{{ __('bom.none') }}</option>
                    @foreach ($foilOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.rolls_per_foil') }}</label>
                <input
                    type="number"
                    step="1"
                    min="0"
                    wire:model.live="yieldRule.foil_per_roll"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="{{ __('bom.no_foil') }}"
                >
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.box_material') }}</label>
                <select
                    wire:model.live="yieldRule.box_material_id"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                >
                    <option value="">{{ __('bom.none') }}</option>
                    @foreach ($boxOptions as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('bom.rolls_per_box') }}</label>
                <input
                    type="number"
                    step="1"
                    min="0"
                    wire:model.live="yieldRule.rolls_per_box"
                    wire:change="recalculate"
                    class="w-full border rounded px-2 py-1 text-sm"
                    placeholder="{{ __('bom.example', ['value' => '200']) }}"
                >
            </div>
        </div>
    </div>

    {{-- Non-geometric BOM items (machine, graphic, other) --}}
    @if (count($items) > 0)
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">{{ __('bom.other_components') }}</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs">
                        <th>{{ __('bom.material') }}</th>
                        <th>{{ __('bom.role') }}</th>
                        <th class="w-36">{{ __('bom.qty_per_roll') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        <tr class="border-t">
                            <td class="py-1">{{ $item['material_name'] }}</td>
                            <td class="py-1 text-gray-500 text-xs">{{ __('bom.roles.' . $item['role']) }}</td>
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
            <h3 class="font-semibold">{{ __('bom.cost_preview') }}</h3>
            <div class="flex items-center gap-2 text-sm">
                <label class="text-gray-500">{{ __('bom.qty') }}:</label>
                <input
                    type="number"
                    min="1"
                    wire:model.live="previewQuantity"
                    wire:change="recalculate"
                    class="w-20 border rounded px-2 py-1 text-sm"
                >
            </div>
        </div>

        <x-cost-problems :problems="$costPreview['problems'] ?? []" />

        {{-- Paper --}}
        @if ($costPreview['breakdown']['paper']['error'] ?? null)
            <x-paper-cost-problem :paper="$costPreview['breakdown']['paper']" />
        @elseif ($costPreview['breakdown']['paper'] ?? null)
            @php $p = $costPreview['breakdown']['paper']; @endphp
            <div>
                <h4 class="font-semibold text-sm mb-1">{{ __('bom.paper') }}</h4>
                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr><td class="text-gray-500 w-40">{{ __('bom.jumbo') }}</td><td>{{ $p['jumbo'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.rolls_per_shaft') }}</td><td>{{ $p['rolls_per_shaft'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.shafts') }}</td><td>{{ $p['shafts'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.gross_area') }}</td><td>{{ $p['gross_area_m2'] }} m²</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.waste') }}</td><td>{{ $p['waste_pct'] }} %</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.net_area') }}</td><td>{{ $p['net_area_m2'] }} m²</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.price_per_m2') }}</td><td>{{ ($p['price_per_m2'] === null ? '—' : number_format($p['price_per_m2'], 6)) }}</td></tr>
                        <tr class="font-semibold border-t"><td>{{ __('bom.paper_cost') }}</td><td>{{ number_format($p['cost'], 4) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Ink --}}
        @if ($costPreview['breakdown']['ink'] ?? null)
            @php $ink = $costPreview['breakdown']['ink']; @endphp
            <div>
                <h4 class="font-semibold text-sm mb-1">{{ __('bom.ink') }}</h4>
                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr><td class="text-gray-500 w-40">{{ __('bom.material') }}</td><td>{{ $ink['material'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.roll_area') }}</td><td>{{ $ink['roll_area_m2'] }} m²</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.kg_per_m2') }}</td><td>{{ $ink['ink_kg_per_m2'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.kg_per_roll') }}</td><td>{{ $ink['ink_kg_per_roll'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.price_per_kg') }}</td><td>{{ ($ink['unit_price'] === null ? '—' : number_format($ink['unit_price'], 4)) }}</td></tr>
                        <tr class="font-semibold border-t"><td>{{ __('bom.ink_cost') }}</td><td>{{ number_format($ink['cost'], 4) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Tube --}}
        @if ($costPreview['breakdown']['tube'] ?? null)
            @php $tube = $costPreview['breakdown']['tube']; @endphp
            <div>
                <h4 class="font-semibold text-sm mb-1">{{ __('bom.tube') }}</h4>
                <table class="w-full text-xs border-collapse">
                    <tbody>
                        <tr><td class="text-gray-500 w-40">{{ __('bom.material') }}</td><td>{{ $tube['material'] }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.qty') }}</td><td>{{ number_format($tube['quantity']) }}</td></tr>
                        <tr><td class="text-gray-500">{{ __('bom.unit_price') }}</td><td>{{ ($tube['unit_price'] === null ? '—' : number_format($tube['unit_price'], 4)) }}</td></tr>
                        <tr class="font-semibold border-t"><td>{{ __('bom.tube_cost') }}</td><td>{{ number_format($tube['cost'], 4) }}</td></tr>
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Machine --}}
        @if (count($costPreview['breakdown']['machine'] ?? []) > 0)
            <div>
                <h4 class="font-semibold text-sm mb-1">{{ __('bom.machine') }}</h4>
                <table class="w-full text-xs border-collapse">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">{{ __('bom.machine') }}</th>
                            <th class="text-right">{{ __('bom.time_h') }}</th>
                            <th class="text-right">{{ __('bom.rate_per_h') }}</th>
                            <th class="text-right">{{ __('bom.cost') }}</th>
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
                                <td class="text-right">{{ ($row['unit_price'] === null ? '—' : number_format($row['unit_price'], 2)) }}</td>
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
                <h4 class="font-semibold text-sm mb-1">{{ __('bom.other_components') }}</h4>
                <table class="w-full text-xs border-collapse">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">{{ __('bom.material') }}</th>
                            <th class="text-left">{{ __('bom.role') }}</th>
                            <th class="text-right">{{ __('bom.qty') }}</th>
                            <th class="text-right">{{ __('bom.unit_price') }}</th>
                            <th class="text-right">{{ __('bom.cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($costPreview['breakdown']['components'] as $row)
                            <tr class="border-t">
                                <td>{{ $row['material'] }}</td>
                                <td>{{ __('bom.roles.' . $row['role']) }}</td>
                                <td class="text-right">{{ number_format($row['quantity'], 4) }}</td>
                                <td class="text-right">{{ ($row['unit_price'] === null ? '—' : number_format($row['unit_price'], 4)) }}</td>
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
                {{ __('bom.setup_cost') }}: <strong>{{ number_format($costPreview['breakdown']['setup'], 4) }}</strong>
            </div>
        @endif

        {{-- Packaging --}}
        @if (count($costPreview['breakdown']['packaging'] ?? []) > 0)
            <div>
                <h4 class="font-semibold text-sm mb-1">{{ __('bom.packaging') }}</h4>
                <table class="w-full text-xs border-collapse">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">{{ __('bom.material') }}</th>
                            <th class="text-right">{{ __('bom.qty') }}</th>
                            <th class="text-right">{{ __('bom.unit_price') }}</th>
                            <th class="text-right">{{ __('bom.cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($costPreview['breakdown']['packaging'] as $row)
                            <tr class="border-t">
                                <td>{{ $row['material'] }}</td>
                                <td class="text-right">{{ $row['quantity'] }}</td>
                                <td class="text-right">{{ ($row['unit_price'] === null ? '—' : number_format($row['unit_price'], 4)) }}</td>
                                <td class="text-right">{{ number_format($row['cost'], 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @isset($costPreview['currency'])
            <p class="text-xs text-gray-400">{{ __('bom.currency_note', ['currency' => $costPreview['currency'], 'date' => $costPreview['price_date']]) }}</p>
        @endisset

        {{-- Totals --}}
        <div class="border-t pt-2 space-y-1 text-sm">
            <div><strong>{{ __('bom.unit_cost') }}:</strong> {{ number_format($costPreview['unit_cost'] ?? 0, 4) }}</div>
            <div><strong>{{ __('bom.total_rolls', ['count' => $previewQuantity]) }}:</strong> {{ number_format($costPreview['total_cost'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <div class="text-xs text-gray-500">{{ __('bom.not_autosaved') }}</div>
        <button
            wire:click="save"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
            {{ __('bom.save') }}
        </button>
    </div>

</div>
