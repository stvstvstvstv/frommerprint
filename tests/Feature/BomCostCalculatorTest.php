<?php

namespace Tests\Feature;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\BomYieldRule;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Material;
use App\Models\Unit;
use App\Services\BomCostCalculator;
use App\Services\CurrencyConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BomCostCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected Currency $ron;
    protected Currency $eur;
    protected Material $finished;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ron = Currency::create(['code' => 'RON', 'name' => 'Romanian Leu']);
        $this->eur = Currency::create(['code' => 'EUR', 'name' => 'Euro']);

        foreach (['pcs', 'sqm', 'kg', 'l', 'hr'] as $code) {
            Unit::create(['code' => $code, 'name' => $code]);
        }

        // 57 mm × 30 m roll, as in docs/bom-costing-explained.md
        $this->finished = $this->material('finished', ['width' => 57, 'length' => 30]);
    }

    // -------------------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------------------

    protected function material(string $type, array $attributes = []): Material
    {
        static $n = 0;
        $n++;

        $pricingUnit = $attributes['pricing_unit'] ?? 'pcs';
        unset($attributes['pricing_unit']);

        return Material::create($attributes + [
            'code'            => "M{$n}",
            'name'            => "{$type} {$n}",
            'material_type'   => $type,
            'unit_id'         => Unit::where('code', 'pcs')->value('id'),
            'currency_id'     => $this->ron->id,
            'pricing_unit_id' => Unit::where('code', $pricingUnit)->value('id'),
            'pricing_per'     => 1,
            'price'           => 0,
        ]);
    }

    protected function bom(array $rule = [], array $items = [], int $setupMinutes = 0): Bom
    {
        $bom = Bom::create([
            'finished_material_id' => $this->finished->id,
            'setup_time_minutes'   => $setupMinutes,
            'active'               => true,
        ]);

        // A yield rule always has a jumbo; tests not about paper get a free one
        $rule['preferred_jumbo_material_id'] ??= $this->material('jumbo', [
            'width' => 1410, 'price' => 0, 'pricing_unit' => 'sqm',
        ])->id;

        BomYieldRule::create($rule + ['bom_id' => $bom->id, 'paper_waste_pct' => 1]);

        foreach ($items as $item) {
            BomItem::create($item + ['bom_id' => $bom->id]);
        }

        return $bom->fresh(['finishedMaterial', 'items.componentMaterial', 'yieldRule']);
    }

    protected function calculate(Bom $bom, int $quantity, string $date = '2026-01-15'): array
    {
        $converter = new CurrencyConverter(Carbon::parse($date));

        return (new BomCostCalculator($bom, $quantity, $converter))->calculate();
    }

    protected function rate(Currency $currency, string $date, float $rate): void
    {
        ExchangeRate::create(['currency_id' => $currency->id, 'rate_date' => $date, 'rate_to_base' => $rate]);
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    public function test_paper_cost_matches_the_documented_example(): void
    {
        $jumbo = $this->material('jumbo', ['width' => 1410, 'price' => 1.20, 'pricing_unit' => 'sqm']);
        $bom   = $this->bom(['preferred_jumbo_material_id' => $jumbo->id]);

        $result = $this->calculate($bom, 1000);
        $paper  = $result['breakdown']['paper'];

        $this->assertSame(24, $paper['rolls_per_shaft']);
        $this->assertSame(42, $paper['shafts']);
        $this->assertEqualsWithDelta(1776.6, $paper['gross_area_m2'], 0.001);
        $this->assertEqualsWithDelta(2153.24, $result['total_cost'], 0.01);
        $this->assertSame([], $result['problems']);
    }

    public function test_price_is_divided_by_pricing_per(): void
    {
        // 25 RON for 10 foil packs = 2.50 per pack; 100 rolls / 10 per pack = 10 packs
        $foil = $this->material('foil', ['price' => 25, 'pricing_per' => 10]);
        $bom  = $this->bom(['foil_material_id' => $foil->id, 'foil_per_roll' => 10]);

        $result = $this->calculate($bom, 100);

        $this->assertEqualsWithDelta(2.5, $result['breakdown']['packaging'][0]['unit_price'], 0.0001);
        $this->assertEqualsWithDelta(25.0, $result['total_cost'], 0.0001);
    }

    public function test_foreign_prices_use_the_latest_rate_on_or_before_the_costing_date(): void
    {
        $this->rate($this->eur, '2026-01-01', 5.0);
        $this->rate($this->eur, '2026-01-10', 5.2);
        $this->rate($this->eur, '2026-01-20', 9.9); // after the costing date: ignored

        $tube = $this->material('tube', ['price' => 2, 'currency_id' => $this->eur->id]);
        $bom  = $this->bom(['tube_material_id' => $tube->id]);

        $result = $this->calculate($bom, 10, '2026-01-15');

        $this->assertEqualsWithDelta(10.4, $result['breakdown']['tube']['unit_price'], 0.0001);
        $this->assertEqualsWithDelta(104.0, $result['total_cost'], 0.0001);
        $this->assertSame('RON', $result['currency']);
    }

    public function test_missing_exchange_rate_is_reported_and_left_out_of_the_total(): void
    {
        $this->rate($this->eur, '2026-02-01', 5.0); // only after the costing date

        $tube = $this->material('tube', ['price' => 2, 'currency_id' => $this->eur->id]);
        $box  = $this->material('box', ['price' => 3]);
        $bom  = $this->bom([
            'tube_material_id' => $tube->id,
            'box_material_id'  => $box->id,
            'rolls_per_box'    => 10,
        ]);

        $result = $this->calculate($bom, 10, '2026-01-15');

        $this->assertEqualsWithDelta(3.0, $result['total_cost'], 0.0001); // box only
        $this->assertCount(1, $result['problems']);
        $this->assertSame('missing_rate', $result['problems'][0]['code']);
        $this->assertSame('EUR', $result['problems'][0]['currency']);
    }

    public function test_ink_priced_per_kg_is_costed(): void
    {
        // 0.004 kg/m² × (0.057 m × 30 m) × 25 RON/kg × 1000 rolls = 171 RON
        $ink = $this->material('ink', ['price' => 25, 'pricing_unit' => 'kg']);
        $bom = $this->bom(['ink_material_id' => $ink->id, 'ink_kg_per_m2' => 0.004]);

        $result = $this->calculate($bom, 1000);

        $this->assertEqualsWithDelta(171.0, $result['total_cost'], 0.0001);
        $this->assertSame([], $result['problems']);
    }

    public function test_ink_not_priced_per_kg_is_reported_and_left_out(): void
    {
        $ink = $this->material('ink', ['price' => 25, 'pricing_unit' => 'l']);
        $bom = $this->bom(['ink_material_id' => $ink->id, 'ink_kg_per_m2' => 0.004]);

        $result = $this->calculate($bom, 1000);

        $this->assertEqualsWithDelta(0.0, $result['total_cost'], 0.0001);
        $this->assertSame('wrong_pricing_unit', $result['problems'][0]['code']);
        $this->assertSame('l', $result['problems'][0]['unit']);
        $this->assertSame('kg', $result['problems'][0]['required']);
    }

    public function test_missing_price_is_reported(): void
    {
        $tube = $this->material('tube', ['price' => null]);
        $bom  = $this->bom(['tube_material_id' => $tube->id]);

        $result = $this->calculate($bom, 10);

        $this->assertSame('missing_price', $result['problems'][0]['code']);
    }

    public function test_tube_bom_item_is_not_counted_on_top_of_the_yield_rule_tube(): void
    {
        $tube = $this->material('tube', ['price' => 1]);
        $bom  = $this->bom(
            ['tube_material_id' => $tube->id],
            [['component_material_id' => $tube->id, 'role' => 'tube', 'quantity' => 1]],
        );

        $result = $this->calculate($bom, 10);

        $this->assertEqualsWithDelta(10.0, $result['total_cost'], 0.0001);
    }

    public function test_machine_time_and_setup_use_the_hourly_rate(): void
    {
        // 120 RON/h; 0.01 h per roll × 100 rolls = 1 h = 120; setup 30 min = 60
        $machine = $this->material('machine', ['price' => 120, 'pricing_unit' => 'hr']);
        $bom     = $this->bom(
            [],
            [['component_material_id' => $machine->id, 'role' => 'machine', 'quantity' => 0.01]],
            setupMinutes: 30,
        );

        $result = $this->calculate($bom, 100);

        $this->assertEqualsWithDelta(60.0, $result['breakdown']['setup'], 0.0001);
        $this->assertEqualsWithDelta(180.0, $result['total_cost'], 0.0001);
    }
}
