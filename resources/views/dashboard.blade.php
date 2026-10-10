<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <livewire:server-clock />
        </div>
    </x-slot>

    @php
        $btn  = 'inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700';
        $btn2 = 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50';
        $card = 'bg-white shadow-sm sm:rounded-lg p-5 space-y-4';
        $statusColors = [
            'draft'         => 'bg-gray-100 text-gray-700',
            'in_production' => 'bg-amber-100 text-amber-800',
            'complete'      => 'bg-green-100 text-green-800',
            'cancelled'     => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="text-sm text-gray-500">
                {{ __('dashboard.intro') }}
            </div>

            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Production orders --}}
                <section class="{{ $card }}">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">{{ __('dashboard.production_orders') }}</h3>
                        <span class="text-xs text-gray-400">{{ __('dashboard.total', ['count' => $orderCounts->sum()]) }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('production-orders.create') }}" class="{{ $btn }}">+ {{ __('dashboard.new_order') }}</a>
                        <a href="{{ route('production-orders.index') }}" class="{{ $btn2 }}">{{ __('dashboard.all_orders') }}</a>
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm">
                        @foreach ($orderStatuses as $status)
                            <a href="{{ route('production-orders.index', ['status' => $status]) }}"
                               class="px-2.5 py-1 rounded-full {{ $statusColors[$status] }} hover:ring-1 hover:ring-gray-300">
                                {{ __('orders.statuses.' . $status) }}
                                <span class="font-semibold">{{ $orderCounts[$status] ?? 0 }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-400 mb-1">{{ __('dashboard.recent') }}</div>
                        @forelse ($recentOrders as $order)
                            <div class="flex items-center justify-between gap-3 py-1.5 border-t border-gray-100 text-sm">
                                <div class="min-w-0">
                                    <span class="text-gray-400">#{{ $order->id }}</span>
                                    <span class="truncate">{{ $order->finishedMaterial?->name ?? '—' }}</span>
                                    <span class="text-gray-500">× {{ number_format($order->planned_rolls) }}</span>
                                    @if ($order->client)
                                        <span class="text-gray-400">· {{ $order->client }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusColors[$order->status] ?? 'bg-gray-100' }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                    @if ($order->isDraft())
                                        <a href="{{ route('production-orders.edit', $order) }}" class="text-indigo-600 hover:underline">{{ __('common.edit') }}</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-400">{{ __('dashboard.no_orders') }}</div>
                        @endforelse
                    </div>
                </section>

                {{-- BOMs & costing --}}
                <section class="{{ $card }}">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">{{ __('dashboard.boms_costing') }}</h3>
                        <span class="text-xs text-gray-400">{{ __('dashboard.bom_count', ['count' => $boms->count()]) }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('materials.index', ['type' => 'finished']) }}" class="{{ $btn2 }}">
                            {{ __('dashboard.pick_finished') }}
                        </a>
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ __('dashboard.empty_bom_note') }}
                    </p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-xs uppercase tracking-wide text-gray-400 text-left">
                                <tr>
                                    <th class="py-1 pr-2 font-normal">{{ __('dashboard.finished_product') }}</th>
                                    <th class="py-1 pr-2 font-normal">{{ __('dashboard.jumbo') }}</th>
                                    <th class="py-1 pr-2 font-normal text-right">{{ __('dashboard.items') }}</th>
                                    <th class="py-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($boms as $bom)
                                    <tr class="border-t border-gray-100">
                                        <td class="py-1.5 pr-2">{{ $bom->finishedMaterial?->name ?? '—' }}</td>
                                        <td class="py-1.5 pr-2 text-gray-500">{{ $bom->yieldRule?->preferredJumboMaterial?->name ?? '—' }}</td>
                                        <td class="py-1.5 pr-2 text-right text-gray-500">{{ $bom->items_count }}</td>
                                        <td class="py-1.5 text-right whitespace-nowrap">
                                            @if ($bom->finishedMaterial)
                                                <a href="{{ route('bom.editor', $bom->finishedMaterial) }}" class="text-indigo-600 hover:underline">{{ __('dashboard.cost_editor') }}</a>
                                                <span class="text-gray-300">|</span>
                                            @endif
                                            <a href="{{ route('bom.items', $bom) }}" class="text-gray-500 hover:underline" title="{{ __('dashboard.legacy_items_hint') }}">{{ __('dashboard.legacy_items') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-2 text-gray-400">{{ __('dashboard.no_boms') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Materials --}}
                <section class="{{ $card }}">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">{{ __('dashboard.materials') }}</h3>
                        <span class="text-xs text-gray-400">{{ __('dashboard.total', ['count' => $materialCounts->sum()]) }}</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('materials.index') }}" class="{{ $btn }}">{{ __('dashboard.all_materials') }}</a>
                    </div>

                    <div class="flex flex-wrap gap-2 text-sm">
                        @foreach ($materialCounts->sortKeys() as $type => $count)
                            <a href="{{ route('materials.index', ['type' => $type]) }}"
                               class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-800 hover:ring-1 hover:ring-indigo-200">
                                {{ __('materials.types.' . $type) }}
                                <span class="font-semibold">{{ $count }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>

                {{-- Pricing & admin --}}
                <section class="{{ $card }}">
                    <h3 class="font-semibold text-gray-800">{{ __('dashboard.pricing_admin') }}</h3>

                    <div class="grid gap-3 sm:grid-cols-2 text-sm">
                        <a href="{{ route('admin.currencies') }}" class="block p-3 rounded border border-gray-200 hover:bg-gray-50">
                            <div class="font-medium text-gray-800">{{ __('dashboard.currencies') }}</div>
                            <div class="text-gray-500">{{ __('dashboard.currencies_defined', ['count' => $currencyCount]) }}</div>
                        </a>
                        <a href="{{ route('admin.exchange-rates') }}" class="block p-3 rounded border border-gray-200 hover:bg-gray-50">
                            <div class="font-medium text-gray-800">{{ __('dashboard.exchange_rates') }}</div>
                            <div class="text-gray-500">{{ __('dashboard.latest_rate', ['date' => $latestRateDate ?? '—']) }}</div>
                        </a>
                        <a href="{{ route('users.index') }}" class="block p-3 rounded border border-gray-200 hover:bg-gray-50">
                            <div class="font-medium text-gray-800">{{ __('dashboard.users') }}</div>
                            <div class="text-gray-500">{{ __('dashboard.user_accounts', ['count' => $userCount]) }}</div>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="block p-3 rounded border border-gray-200 hover:bg-gray-50">
                            <div class="font-medium text-gray-800">{{ __('dashboard.my_profile') }}</div>
                            <div class="text-gray-500">{{ __('dashboard.profile_hint') }}</div>
                        </a>
                    </div>

                    <p class="text-xs text-gray-500">
                        {{ __('dashboard.bnr_note') }}
                        (<code class="font-mono">php artisan rates:fetch-bnr</code>).
                    </p>
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
