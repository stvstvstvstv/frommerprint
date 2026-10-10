<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">{{ __('orders.index_title') }}</h2>
        <a href="{{ route('production-orders.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
            + {{ __('orders.new') }}
        </a>
    </div>

    @if (session('status'))
        <div class="bg-green-100 text-green-800 text-sm px-4 py-3 rounded">
            {{ session('status') }}
        </div>
    @endif

    {{-- Status filter --}}
    <div class="flex gap-2 text-sm flex-wrap">
        @foreach ([
            ''              => __('common.all'),
            'draft'         => __('orders.statuses.draft'),
            'in_production' => __('orders.statuses.in_production'),
            'complete'      => __('orders.statuses.complete'),
            'cancelled'     => __('orders.statuses.cancelled'),
        ] as $value => $label)
            <button wire:click="$set('statusFilter', '{{ $value }}')"
                    class="px-3 py-1 rounded border
                        {{ $statusFilter === $value
                            ? 'bg-gray-800 text-white border-gray-800'
                            : 'bg-white text-gray-600 border-gray-300 hover:border-gray-500' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 text-left">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">{{ __('orders.columns.client') }}</th>
                    <th class="px-4 py-3">{{ __('orders.columns.product') }}</th>
                    <th class="px-4 py-3">{{ __('orders.columns.jumbo') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('orders.columns.qty') }}</th>
                    <th class="px-4 py-3">{{ __('orders.columns.status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('orders.columns.cost') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('orders.columns.unit_cost') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400">{{ $order->id }}</td>

                        <td class="px-4 py-3">{{ $order->client ?? '—' }}</td>

                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $order->finishedMaterial->name }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $order->finishedMaterial->code }}
                                &middot; {{ $order->snapshot_finished_width_mm }}mm
                                × {{ $order->snapshot_finished_length_m }}m
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div>{{ $order->jumboMaterial->name }}</div>
                            <div class="text-xs text-gray-400">
                                {{ $order->jumboMaterial->code }}
                                &middot; {{ $order->snapshot_jumbo_width_mm }}mm
                            </div>
                        </td>

                        <td class="px-4 py-3 text-right">{{ number_format($order->planned_rolls) }}</td>

                        <td class="px-4 py-3">
                            @if ($order->isDraft())
                                <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-800">{{ $order->statusLabel() }}</span>
                            @elseif ($order->isInProduction())
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">{{ $order->statusLabel() }}</span>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $order->started_at->translatedFormat('d M Y') }}</div>
                            @elseif ($order->isComplete())
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">{{ $order->statusLabel() }}</span>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $order->completed_at->translatedFormat('d M Y') }}</div>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">{{ $order->statusLabel() }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            @if ($order->confirmed_total_cost)
                                {{ number_format($order->confirmed_total_cost, 2) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            @if ($order->confirmed_total_cost && $order->planned_rolls)
                                {{ number_format($order->confirmed_total_cost / $order->planned_rolls, 4) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                            @if ($order->isDraft())
                                <a href="{{ route('production-orders.edit', $order) }}"
                                   class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 inline-block">
                                    {{ __('common.edit') }}
                                </a>
                                <button wire:click="startProduction({{ $order->id }})"
                                        wire:confirm="{{ __('orders.confirm_start', ['id' => $order->id]) }}"
                                        class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    → {{ __('orders.statuses.in_production') }}
                                </button>
                                <button wire:click="cancel({{ $order->id }})"
                                        wire:confirm="{{ __('orders.confirm_cancel', ['id' => $order->id]) }}"
                                        class="text-xs px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                    {{ __('common.cancel') }}
                                </button>
                            @elseif ($order->isInProduction())
                                <button wire:click="complete({{ $order->id }})"
                                        wire:confirm="{{ __('orders.confirm_complete', ['id' => $order->id]) }}"
                                        class="text-xs px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                    → {{ __('orders.statuses.complete') }}
                                </button>
                                <button wire:click="cancel({{ $order->id }})"
                                        wire:confirm="{{ __('orders.confirm_cancel', ['id' => $order->id]) }}"
                                        class="text-xs px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                    {{ __('common.cancel') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400 text-sm">
                            {{ __('orders.empty') }}
                            <a href="{{ route('production-orders.create') }}" class="text-blue-600 hover:underline">{{ __('orders.create_one') }}</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $orders->links() }}</div>

</div>
