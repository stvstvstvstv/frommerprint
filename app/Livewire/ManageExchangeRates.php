<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Currency;
use App\Models\ExchangeRate;

class ManageExchangeRates extends Component
{
    public $currencyId = '';
    public $rateDate = '';
    public $rate = '';

    public function mount()
    {
        $this->rateDate = now()->toDateString();
    }

    public function save()
    {
        $this->validate([
            'currencyId' => 'required|exists:currencies,id',
            'rateDate'   => 'required|date',
            'rate'       => 'required|numeric|min:0.000001',
        ]);

        ExchangeRate::updateOrCreate(
            ['currency_id' => $this->currencyId, 'rate_date' => $this->rateDate],
            ['rate_to_base' => $this->rate]
        );

        $this->rate = '';
    }

    public function render()
    {
        return view('livewire.manage-exchange-rates', [
            'currencies' => Currency::orderByRaw("CASE WHEN code = 'RON' THEN 0 ELSE 1 END")
                ->orderBy('code')
                ->get(),
            'rates' => ExchangeRate::with('currency')
                ->orderByDesc('rate_date')
                ->orderBy('currency_id')
                ->limit(200)
                ->get(),
        ]);
    }
}
