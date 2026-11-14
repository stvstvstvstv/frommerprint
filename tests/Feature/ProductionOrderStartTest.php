<?php

namespace Tests\Feature;

use App\Livewire\ProductionOrderCreate;
use App\Livewire\ProductionOrdersIndex;
use App\Models\Bom;
use App\Models\BomYieldRule;
use App\Models\Currency;
use App\Models\Material;
use App\Models\ProductionOrder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The cost frozen when an order starts must be the cost the order form
 * previewed, so the order's own values must replace the BOM's the same way.
 */
class ProductionOrderStartTest extends TestCase
{
    use RefreshDatabase;

    public function test_emptied_ink_field_means_no_ink_in_preview_and_frozen_cost(): void
    {
        $ron = Currency::create(['code' => 'RON', 'name' => 'Romanian Leu']);
        foreach (['pcs', 'sqm', 'kg'] as $code) {
            Unit::create(['code' => $code, 'name' => $code]);
        }

        $material = fn (string $type, string $unit, float $price, array $attributes = []) => Material::create($attributes + [
            'code'            => $type,
            'name'            => $type,
            'material_type'   => $type,
            'unit_id'         => Unit::where('code', 'pcs')->value('id'),
            'currency_id'     => $ron->id,
            'pricing_unit_id' => Unit::where('code', $unit)->value('id'),
            'pricing_per'     => 1,
            'price'           => $price,
            'active'          => true,
        ]);

        $finished = $material('finished', 'pcs', 0, ['width' => 57, 'length' => 30, 'gsm' => 48]);
        $jumbo    = $material('jumbo', 'sqm', 0, ['width' => 1410, 'gsm' => 48]);
        $tube     = $material('tube', 'pcs', 1);
        $ink      = $material('ink', 'kg', 25);

        $bom = Bom::create(['finished_material_id' => $finished->id, 'setup_time_minutes' => 0, 'active' => true]);
        BomYieldRule::create([
            'bom_id'                      => $bom->id,
            'preferred_jumbo_material_id' => $jumbo->id,
            'paper_waste_pct'             => 1,
            'ink_kg_per_m2'               => 0.004,
            'ink_material_id'             => $ink->id,
            'tube_material_id'            => $tube->id,
        ]);

        $user = User::factory()->create();

        // Picking the product pre-fills the BOM's ink; the user then empties it
        $form = Livewire::actingAs($user)
            ->test(ProductionOrderCreate::class)
            ->call('selectFinishedMaterial', $finished->id)
            ->set('jumboMaterialId', $jumbo->id)
            ->set('plannedRolls', 10)
            ->set('overrideInkKgPerM2', null)
            ->call('recalculate');

        // 10 tubes × 1 RON, no ink
        $preview = $form->get('costPreview')['total_cost'];
        $this->assertEqualsWithDelta(10.0, $preview, 0.0001);

        $form->call('save');
        $order = ProductionOrder::sole();
        $this->assertNull($order->override_ink_kg_per_m2);

        Livewire::actingAs($user)
            ->test(ProductionOrdersIndex::class)
            ->call('startProduction', $order->id);

        $order->refresh();
        $this->assertTrue($order->isInProduction());
        $this->assertEqualsWithDelta($preview, (float) $order->confirmed_total_cost, 0.0001);
    }
}
