<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use SimpleXMLElement;

class FetchBnrExchangeRates extends Command
{
    protected $signature = 'rates:fetch-bnr';
    protected $description = 'Fetch daily exchange rates from National Bank of Romania';

    public function handle(): int
    {
        $response = Http::timeout(10)->get('https://curs.bnr.ro/nbrfxrates.xml');

        if (! $response->successful()) {
            $this->error('Failed to fetch BNR XML');
            return self::FAILURE;
        }

        // Parse XML
        $xml = new SimpleXMLElement($response->body());
        $xml->registerXPathNamespace('ns', 'https://www.bnr.ro/xsd');

        $cube = $xml->xpath('//ns:Cube')[0] ?? null;

        if (! $cube) {
            $this->warn('No rate cube found in BNR XML');
            return self::SUCCESS;
        }

        $rateDate = Carbon::parse((string) $cube['date'])->toDateString();

        // Load all currencies, indexed by code
        $currencies = Currency::all()->keyBy('code');

        // Idempotency per currency: keep any rate that already exists for
        // this date (e.g. entered by hand) and only add the missing ones.
        $existing = ExchangeRate::where('rate_date', $rateDate)
            ->pluck('currency_id')
            ->flip();

        $imported = [];
        $skipped  = [];

        $store = function (Currency $currency, float $rate) use ($rateDate, $existing, &$imported, &$skipped) {
            if ($existing->has($currency->id)) {
                $skipped[] = $currency->code;
                return;
            }

            ExchangeRate::create([
                'currency_id'  => $currency->id,
                'rate_date'    => $rateDate,
                'rate_to_base' => $rate,
            ]);

            $imported[] = $currency->code;
        };

        // 1️⃣ Always insert base currency (RON)
        $baseCurrency = $currencies->get('RON');

        if (! $baseCurrency) {
            $this->error('Base currency RON not found in currencies table');
            return self::FAILURE;
        }

        $store($baseCurrency, 1.0);

        // 2️⃣ Process all rates published by BNR
        foreach ($cube->Rate as $rateNode) {
            $code = (string) $rateNode['currency'];

            // Only insert currencies we actually track
            if (! $currencies->has($code)) {
                continue;
            }

            $value = (float) $rateNode;
            $multiplier = (int) ($rateNode['multiplier'] ?? 1);

            // Normalize (BNR publishes e.g. HUF per 100 units)
            $normalizedRate = $value / $multiplier;

            $store($currencies[$code], $normalizedRate);
        }

        $this->info(sprintf(
            'BNR exchange rates for %s: imported %s; already present, skipped %s',
            $rateDate,
            $imported ? implode(', ', $imported) : 'none',
            $skipped ? implode(', ', $skipped) : 'none',
        ));

        return self::SUCCESS;
    }
}
