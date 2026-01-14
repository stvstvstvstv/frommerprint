<div>
    <h1 class="text-xl font-semibold mb-4">Exchange Rates</h1>

    <div class="grid grid-cols-4 gap-3 mb-4">
        <select wire:model="currencyId" class="border rounded px-3 py-2">
            <option value="">All</option>
            @foreach ($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->code }}</option>
            @endforeach
        </select>

        <input type="date" wire:model="rateDate" class="border rounded px-3 py-2">
        <input type="number" wire:model="rate" step="0.000001" class="border rounded px-3 py-2">

        <button wire:click="save" class="bg-blue-600 text-white rounded px-3">
            Save
        </button>
    </div>

    <table class="w-full border-collapse border">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">Currency</th>
                <th class="border px-3 py-2">Date</th>
                <th class="border px-3 py-2 text-right">Rate</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($rates as $rate)
            <tr>
                <td class="border px-3 py-2">{{ $rate->currency->code }}</td>
                <td class="border px-3 py-2">{{ $rate->rate_date->format('Y-m-d') }}</td>
                <td class="border px-3 py-2 text-right">
                    {{ number_format($rate->rate_to_base, 6) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
