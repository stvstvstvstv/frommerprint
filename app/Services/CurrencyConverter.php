<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Converts amounts into the base currency (config('currency.base'), RON)
 * using the latest exchange rate on or before a given date.
 *
 * Rates are cached per currency for the lifetime of the instance, so one
 * converter can be shared across a whole cost calculation.
 */
class CurrencyConverter
{
    protected CarbonInterface $date;

    /** @var array<int, float|null> rate_to_base keyed by currency id */
    protected array $rates = [];

    public function __construct(?CarbonInterface $date = null)
    {
        $this->date = $date ?? Carbon::today();
    }

    public function date(): CarbonInterface
    {
        return $this->date;
    }

    public static function baseCode(): string
    {
        return config('currency.base');
    }

    /**
     * Amount in the base currency, or null when no rate exists on or
     * before the conversion date.
     */
    public function toBase(float $amount, Currency $currency): ?float
    {
        $rate = $this->rateFor($currency);

        return $rate === null ? null : $amount * $rate;
    }

    protected function rateFor(Currency $currency): ?float
    {
        if ($currency->code === self::baseCode()) {
            return 1.0;
        }

        if (! array_key_exists($currency->id, $this->rates)) {
            $rate = ExchangeRate::where('currency_id', $currency->id)
                ->whereDate('rate_date', '<=', $this->date)
                ->orderByDesc('rate_date')
                ->value('rate_to_base');

            $this->rates[$currency->id] = $rate === null ? null : (float) $rate;
        }

        return $this->rates[$currency->id];
    }
}
