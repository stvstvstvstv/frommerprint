<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold">{{ __('currencies.title') }}</h1>

        <button
            wire:click="create"
            class="px-4 py-2 bg-blue-600 text-white rounded">
            {{ __('currencies.add') }}
        </button>
    </div>

    <table class="w-full border-collapse border mb-6">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2 text-left">{{ __('currencies.code') }}</th>
                <th class="border px-3 py-2 text-left">{{ __('currencies.name') }}</th>
                <th class="border px-3 py-2 text-right">{{ __('currencies.latest_rate') }}</th>
                <th class="border px-3 py-2 w-24">{{ __('common.actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($currencies as $currency)
            <tr @if ($currency->code === config('currency.base')) class="bg-yellow-50 font-semibold text-blue-600" @endif>
                <td class="border px-3 py-2">
                    {{ $currency->code }}
                </td>
                <td class="border px-3 py-2">
                    {{ $currency->name }}
                </td>

                <td class="border px-3 py-2 text-right">
                    @if ($currency->latestExchangeRate)
                        {{ $currency->latestExchangeRate->rate_to_base }} ({{ $currency->latestExchangeRate->rate_date->format('Y-m-d') }})
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="border px-3 py-2 text-center">
                    <button
                        wire:click="edit({{ $currency->id }})"
                        class="text-blue-600 hover:underline">
                        {{ __('common.edit') }}
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Modal --}}
    @if ($showForm)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-full max-w-md">

                <h2 class="text-lg font-semibold mb-4">
                    {{ $currencyId ? __('currencies.edit') : __('currencies.add') }}
                </h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium">{{ __('currencies.code') }}</label>
                    <input
                        type="text"
                        wire:model.defer="code"
                        maxlength="3"
                        class="w-full border rounded px-3 py-2 uppercase"
                        {{ $currencyId ? 'readonly' : '' }}
                    >
                    @error('code')
                        <div class="text-red-600 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">{{ __('currencies.name') }}</label>
                    <input
                        type="text"
                        wire:model.defer="name"
                        class="w-full border rounded px-3 py-2"
                    >
                    @error('name')
                        <div class="text-red-600 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        wire:click="cancel"
                        class="px-4 py-2 border rounded">
                        {{ __('common.cancel') }}
                    </button>

                    <button
                        wire:click="save"
                        class="px-4 py-2 bg-blue-600 text-white rounded">
                        {{ __('common.save') }}
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>