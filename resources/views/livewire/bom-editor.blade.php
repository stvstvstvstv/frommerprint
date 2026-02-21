<div class="space-y-6">

    @if (session('status'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-semibold">
            BOM for: {{ $bom->finishedMaterial->name }}
        </h2>

        <div class="mt-2 text-sm text-gray-600">
            Setup time: {{ $bom->setup_time_minutes }} minutes
        </div>
    </div>

    {{-- Papers --}}
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Paper</h3>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th>Material</th>
                    <th>Qty / unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @if ($item['role'] === 'paper')
                        <tr>
                            <td>{{ $item['material_name'] }}</td>
                            <td class="w-32">
                                <input
                                    type="number"
                                    step="0.0001"
                                    class="w-full border rounded px-2 py-1 text-sm"
                                    wire:model.debounce.500ms="items.{{ $loop->index }}.quantity"
                                    wire:change="recalculate"
                                >
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Inks --}}
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Inks</h3>

        <ul class="text-sm space-y-1">
            @foreach ($items as $item)
                @if ($item['role'] === 'ink')
                    <li class="flex items-center gap-2">
                        <span class="flex-1">{{ $item['material_name'] }}</span>

                        <input
                            type="number"
                            step="0.0001"
                            class="w-24 border rounded px-2 py-1 text-sm"
                            wire:model.debounce.500ms="items.{{ $loop->index }}.quantity"
                            wire:change="recalculate"
                        >
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="mt-2 text-xs text-gray-500">
            Colours detected: {{ collect($items)->where('role', 'ink')->count() }}
        </div>
    </div>

    {{-- Machine --}}
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Machine</h3>

        @foreach ($items as $item)
            @if ($item['role'] === 'machine')
                <div class="text-sm">
                    {{ $item['material_name'] }} – <input
    type="number"
    step="0.01"
    class="w-24 border rounded px-2 py-1 text-sm"
    wire:model.debounce.500ms="items.{{ $loop->index }}.quantity"
    wire:change="recalculate"
/> h / unit
                </div>
            @endif
        @endforeach
    </div>

    {{-- Packaging --}}
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Packaging</h3>

        <div class="text-sm">
            @if ($packaging['inner_material_name'])
                Inner: {{ $packaging['inner_material_name'] }} ({{ $packaging['inner_qty'] }} units)
            @else
                Inner: none
            @endif
        </div>

        <div class="text-sm">
            Outer: {{ $packaging['outer_material_name'] }} ({{ $packaging['outer_qty'] }} packs)
        </div>
    </div>

    {{-- Cost Preview (Detailed) --}}
    <div class="bg-gray-50 p-4 rounded shadow space-y-4">
        <h3 class="font-semibold">Cost Preview (Detailed)</h3>

        <div class="text-xs text-gray-500">
            Preview only — changes not saved
        </div>

        {{-- Materials --}}
        <div>
            <h4 class="font-semibold text-sm mb-1">Materials</h4>
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
                    @foreach ($costPreview['breakdown']['materials'] ?? [] as $row)
                        <tr class="border-t">
                            <td>{{ $row['material'] }}</td>
                            <td class="text-right">{{ number_format($row['quantity'], 4) }}</td>
                            <td class="text-right">{{ number_format($row['unit_price'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($row['cost'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Machine --}}
        <div>
            <h4 class="font-semibold text-sm mb-1">Machine</h4>
            <table class="w-full text-xs border-collapse">
                <thead class="text-gray-500">
                    <tr>
                        <th class="text-left">Machine</th>
                        <th class="text-right">Time</th>
                        <th class="text-right">Rate / h</th>
                        <th class="text-right">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($costPreview['breakdown']['machine'] ?? [] as $row)
                    @php
                        $totalMinutes = (int) round(($row['time'] ?? 0) * 60);
                        $hours = intdiv($totalMinutes, 60);
                        $minutes = $totalMinutes % 60;
                    @endphp
                        <tr class="border-t">
                            <td>{{ $row['machine'] }}</td>
                            <td class="text-right">{{ number_format($row['time'], 2) }} - ({{ $hours }}h {{ $minutes }}m)</td>
                            <td class="text-right">{{ number_format($row['unit_price'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($row['cost'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Setup --}}
        <div>
            <h4 class="font-semibold text-sm mb-1">Setup</h4>
            <div class="text-xs">
                Setup cost:
                <strong>{{ number_format($costPreview['breakdown']['setup'] ?? 0, 2) }}</strong>
            </div>
        </div>

        {{-- Packaging --}}
        <div>
            <h4 class="font-semibold text-sm mb-1">Packaging</h4>
            <table class="w-full text-xs border-collapse">
                <thead class="text-gray-500">
                    <tr>
                        <th class="text-left">Packaging</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Unit price</th>
                        <th class="text-right">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($costPreview['breakdown']['packaging'] ?? [] as $row)
                        <tr class="border-t">
                            <td>{{ $row['material'] }}</td>
                            <td class="text-right">{{ $row['quantity'] }}</td>
                            <td class="text-right">{{ number_format($row['unit_price'], 2) }}</td>
                            <td class="text-right">{{ number_format($row['cost'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="border-t pt-2 text-sm space-y-1">
            <div>
                <strong>Unit cost:</strong>
                {{ number_format($costPreview['unit_cost'] ?? 0, 2) }}
            </div>
            <div>
                <strong>Total cost ({{ $previewQuantity }} units):</strong>
                {{ number_format($costPreview['total_cost'] ?? 0, 2) }}
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <div class="text-xs text-gray-500">
            Changes are not saved automatically
        </div>

        <button
            wire:click="save"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
            Save BOM
        </button>
    </div>

</div>
