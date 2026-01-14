<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Currency;

class ManageCurrencies extends Component
{
    public $currencies;

    // Form fields
    public $currencyId = null;
    public $code = '';
    public $name = '';

    public $showForm = false;

    public function mount()
    {
        $this->loadCurrencies();
    }

    public function loadCurrencies()
    {
        $this->currencies = Currency::orderBy('code')->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $currency = Currency::findOrFail($id);

        $this->currencyId = $currency->id;
        $this->code = $currency->code;
        $this->name = $currency->name;

        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|string|size:3|unique:currencies,code,' . $this->currencyId,
            'name' => 'required|string|max:255',
        ]);

        Currency::updateOrCreate(
            ['id' => $this->currencyId],
            [
                'code' => strtoupper($this->code),
                'name' => $this->name,
            ]
        );

        $this->showForm = false;
        $this->resetForm();
        $this->loadCurrencies();
    }

    public function cancel()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->currencyId = null;
        $this->code = '';
        $this->name = '';
    }

    public function render()
    {
        return view('livewire.manage-currencies');
    }
}