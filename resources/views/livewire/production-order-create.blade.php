<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">{{ __('orders.form.new_title') }}</h2>
        <a href="{{ route('production-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            &larr; {{ __('orders.form.back') }}
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
                <h3 class="font-semibold text-gray-700">{{ __('orders.form.product') }}</h3>

                {{-- Finished material combobox --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.finished_material') }}</label>

                    @if ($finishedMaterialId)
                        {{-- Selected state --}}
                        <div class="flex items-center gap-2 border rounded px-2 py-1 bg-gray-50">
                            <span class="text-sm flex-1 truncate">{{ $selectedFinishedName }}</span>
                            @if ($selectedFinishedCode)
                                <span class="text-xs text-gray-400 shrink-0">{{ $selectedFinishedCode }}</span>
                            @endif
                            <button wire:click="clearFinishedMaterial"
                                    class="text-gray-400 hover:text-red-500 text-xs leading-none"
                                    title="{{ __('orders.form.clear_selection') }}">&times;</button>
                        </div>
                        @if ($selectedFinishedDims)
                            <div class="text-xs text-gray-400 mt-1 flex gap-3">
                                <span>{{ $selectedFinishedDims['width'] ?? '—' }} mm</span>
                                <span>× {{ $selectedFinishedDims['length'] ?? '—' }} m</span>
                                <span>{{ $selectedFinishedDims['gsm'] ?? '—' }} gsm</span>
                            </div>
                        @endif
                    @else
                        {{-- Pure Livewire combobox. Backdrop (z-10) catches outside
                             clicks; dropdown (z-20) sits above it. No Alpine. --}}
                        <div class="relative z-20">
                            <input
                                type="text"
                                wire:model.live.debounce.200ms="finishedSearch"
                                wire:focus="openFinishedDropdown"
                                wire:keydown.escape="closeFinishedDropdown"
                                placeholder="{{ __('orders.form.search_placeholder') }}"
                                autocomplete="off"
                                class="w-full border rounded px-2 py-1 text-sm"
                            >

                            @if ($showFinishedDropdown)
                                {{-- Transparent backdrop: clicks outside dropdown land here --}}
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
                                        <div class="px-3 py-2 text-sm text-gray-400 italic">{{ __('orders.form.no_results') }}</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif

                    @error('finishedMaterialId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.client') }}</label>
                    <input type="text" wire:model.live="client"
                           class="w-full border rounded px-2 py-1 text-sm" placeholder="{{ __('orders.form.client_placeholder') }}">
                    @error('client') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.planned_qty') }}</label>
                    <input type="number" min="1" wire:model.live="plannedRolls"
                           class="w-full border rounded px-2 py-1 text-sm">
                    @error('plannedRolls') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Materials --}}
            <div class="bg-white p-4 rounded shadow space-y-4">
                <h3 class="font-semibold text-gray-700">{{ __('orders.form.materials') }}</h3>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.jumbo_roll') }}</label>
                    <select wire:model.live="jumboMaterialId"
                            class="w-full border rounded px-2 py-1 text-sm">
                        <option value="">{{ __('orders.form.select_jumbo') }}</option>
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
                        <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.ink_kg_m2') }}</label>
                        <input type="number" step="0.000001" min="0"
                               wire:model.live="overrideInkKgPerM2"
                               placeholder="{{ __('orders.form.from_bom') }}"
                               class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.rolls_per_foil') }}</label>
                        <input type="number" step="1" min="0"
                               wire:model.live="overrideFoilPerRoll"
                               class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ __('orders.form.rolls_per_box') }}</label>
                        <input type="number" step="1" min="0"
                               wire:model.live="overrideRollsPerBox"
                               class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button wire:click="save"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    {{ __('orders.form.create_draft') }}
                </button>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- RIGHT: Cost Preview                                               --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="bg-gray-50 p-4 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-700">{{ __('orders.form.cost_preview') }}
                @if($plannedRolls > 0)
                    <span class="font-normal text-gray-400 text-sm">— {{ __('orders.form.rolls_count', ['count' => number_format($plannedRolls)]) }}</span>
                @endif
            </h3>

            @if (empty($costPreview))
                <p class="text-sm text-gray-400 italic">{{ __('orders.form.preview_hint_create') }}</p>
            @else

                <x-cost-problems :problems="$costPreview['problems'] ?? []" />

                {{-- Paper --}}
                @if ($costPreview['breakdown']['paper']['error'] ?? null)
                    <x-paper-cost-problem :paper="$costPreview['breakdown']['paper']" />
                @elseif ($costPreview['breakdown']['paper'] ?? null)
                    @php $p = $costPreview['breakdown']['paper']; @endphp
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('orders.form.paper') }}</h4>
                        <table class="w-full text-xs border-collapse">
                            <tbody>
                                <tr><td class="text-gray-500 w-40">{{ __('orders.form.jumbo') }}</td><td>{{ $p['jumbo'] }}</td></tr>
                                <tr><td class="text-gray-500">{{ __('orders.form.rolls_per_shaft') }}</td><td>{{ $p['rolls_per_shaft'] }}</td></tr>
                                <tr><td class="text-gray-500">{{ __('orders.form.shafts') }}</td><td>{{ $p['shafts'] }}</td></tr>
                                <tr><td class="text-gray-500">{{ __('orders.form.net_area') }}</td><td>{{ $p['net_area_m2'] }} m²</td></tr>
                                <tr><td class="text-gray-500">{{ __('orders.form.price_per_m2') }}</td><td>{{ ($p['price_per_m2'] === null ? '—' : number_format($p['price_per_m2'], 4)) }}</td></tr>
                                <tr class="font-semibold border-t"><td>{{ __('orders.form.paper_cost') }}</td><td>{{ number_format($p['cost'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Ink --}}
                @if ($costPreview['breakdown']['ink'] ?? null)
                    @php $ink = $costPreview['breakdown']['ink']; @endphp
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('orders.form.ink') }}</h4>
                        <table class="w-full text-xs border-collapse">
                            <tbody>
                                <tr><td class="text-gray-500 w-40">{{ __('orders.form.material') }}</td><td>{{ $ink['material'] }}</td></tr>
                                <tr><td class="text-gray-500">{{ __('orders.form.kg_per_roll') }}</td><td>{{ $ink['ink_kg_per_roll'] }}</td></tr>
                                <tr class="font-semibold border-t"><td>{{ __('orders.form.ink_cost') }}</td><td>{{ number_format($ink['cost'], 2) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Packaging --}}
                @if (count($costPreview['breakdown']['packaging'] ?? []) > 0)
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('orders.form.packaging') }}</h4>
                        <table class="w-full text-xs border-collapse">
                            <thead class="text-gray-500">
                                <tr><th class="text-left">{{ __('orders.form.material') }}</th><th class="text-right">{{ __('orders.form.qty') }}</th><th class="text-right">{{ __('orders.form.cost') }}</th></tr>
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
                        <h4 class="text-sm font-medium text-gray-600 mb-1">{{ __('orders.form.other_components') }}</h4>
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

                @isset($costPreview['currency'])
                    <p class="text-xs text-gray-400">{{ __('bom.currency_note', ['currency' => $costPreview['currency'], 'date' => $costPreview['price_date']]) }}</p>
                @endisset

                {{-- Totals --}}
                <div class="border-t pt-3 space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('orders.form.unit_cost') }}</span>
                        <strong>{{ number_format($costPreview['unit_cost'], 4) }}</strong>
                    </div>
                    <div class="flex justify-between text-base font-semibold">
                        <span>{{ __('orders.form.total_rolls', ['count' => number_format($plannedRolls)]) }}</span>
                        <span>{{ number_format($costPreview['total_cost'], 2) }}</span>
                    </div>
                </div>

            @endif
        </div>

    </div>
</div>
