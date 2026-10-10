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

        // Idempotency: do nothing if already imported
        if (ExchangeRate::where('rate_date', $rateDate)->exists()) {
            $this->info("Exchange rates already exist for {$rateDate}");
            return self::SUCCESS;
        }

        // Load all currencies, indexed by code
        $currencies = Currency::all()->keyBy('code');

        // 1️⃣ Always insert base currency (RON)
        $baseCurrency = $currencies->get('RON');

        if (! $baseCurrency) {
            $this->error('Base currency RON not found in currencies table');
            return self::FAILURE;
        }

        ExchangeRate::create([
            'currency_id'  => $baseCurrency->id,   // should be 1
            'rate_date'    => $rateDate,
            'rate_to_base' => 1.000000,
        ]);

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

            ExchangeRate::create([
                'currency_id'  => $currencies[$code]->id,
                'rate_date'    => $rateDate,
                'rate_to_base' => $normalizedRate,
            ]);
        }

        $this->info("BNR exchange rates imported for {$rateDate}");

        return self::SUCCESS;
    }
}
