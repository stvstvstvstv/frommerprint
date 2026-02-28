<?php

namespace App\Services;

use App\Models\Bom;

/**
 * Calculates the cost of producing $quantity finished rolls from a BOM.
 *
 * Paper and ink costs are derived geometrically from the yield rule and
 * material dimensions. BOM items cover only non-geometric components
 * (machine time, tubes, graphic artwork, misc).
 */
class BomCostCalculator
{
    // Roles whose cost is now driven by the yield rule, not BOM item quantities.
    protected const GEOMETRIC_ROLES = ['paper', 'ink', 'jumbo', 'foil', 'box'];

    protected Bom $bom;
    protected int $quantity;

    public function __construct(Bom $bom, int $quantity)
    {
        $this->bom = $bom;
        $this->quantity = $quantity;
    }

    public function calculate(): array
    {
        $yieldRule  = $this->bom->yieldRule;
        $finished   = $this->bom->finishedMaterial;

        $paper      = $this->calculatePaperCost($yieldRule, $finished);
        $ink        = $this->calculateInkCost($yieldRule, $finished);
        $components = $this->calculateComponentCosts();
        $setup      = $this->calculateSetupCost();
        $packaging  = $this->calculatePackagingCost($yieldRule);

        $totalCost = $paper['cost']
            + $ink['cost']
            + $components['cost']
            + $setup
            + $packaging['cost'];

        return [
            'unit_cost'  => $totalCost / $this->quantity,
            'total_cost' => $totalCost,

            'breakdown' => [
                'paper'      => $paper['breakdown'],
                'ink'        => $ink['breakdown'],
                'components' => $components['breakdown'],
                'machine'    => $components['machine_breakdown'],
                'setup'      => $setup,
                'packaging'  => $packaging['breakdown'],
            ],

            'packaging_quantities' => $packaging['quantities'],
        ];
    }

    // -------------------------------------------------------------------------
    // Paper (geometric)
    // -------------------------------------------------------------------------

    protected function calculatePaperCost(mixed $yieldRule, mixed $finished): array
    {
        if (! $yieldRule?->preferredJumboMaterial || ! $finished->width || ! $finished->length) {
            return ['cost' => 0.0, 'breakdown' => null];
        }

        $jumbo = $yieldRule->preferredJumboMaterial;

        // Rolls cut per shaft pass (how many finished-width strips fit across the jumbo)
        $rollsPerShaft = (int) floor($jumbo->width / $finished->width);

        if ($rollsPerShaft <= 0) {
            return ['cost' => 0.0, 'breakdown' => ['error' => 'Finished roll wider than jumbo']];
        }

        // Number of shaft passes required for the full order
        $shafts = (int) ceil($this->quantity / $rollsPerShaft);

        // Paper area consumed (m²): shafts × jumbo_width_m × roll_length_m
        $jumboWidthM  = $jumbo->width / 1000;
        $grossAreaM2  = $shafts * $jumboWidthM * $finished->length;

        // Apply production waste
        $wastePct    = (float) ($yieldRule->paper_waste_pct ?? 1.0);
        $netAreaM2   = $grossAreaM2 * (1 + $wastePct / 100);

        // Jumbo is priced per m²
        $pricePerM2  = (float) $jumbo->price;
        $cost        = $netAreaM2 * $pricePerM2;

        return [
            'cost' => $cost,
            'breakdown' => [
                'jumbo'            => $jumbo->name,
                'jumbo_width_mm'   => $jumbo->width,
                'finished_width_mm' => $finished->width,
                'rolls_per_shaft'  => $rollsPerShaft,
                'shafts'           => $shafts,
                'gross_area_m2'    => round($grossAreaM2, 4),
                'waste_pct'        => $wastePct,
                'net_area_m2'      => round($netAreaM2, 4),
                'price_per_m2'     => $pricePerM2,
                'cost'             => $cost,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Ink (geometric)
    // -------------------------------------------------------------------------

    protected function calculateInkCost(mixed $yieldRule, mixed $finished): array
    {
        if (! $yieldRule?->ink_kg_per_m2 || ! $yieldRule->inkMaterial || ! $finished->width || ! $finished->length) {
            return ['cost' => 0.0, 'breakdown' => null];
        }

        // Roll surface area in m² (width stored in mm, length in m)
        $rollAreaM2 = ($finished->width / 1000) * $finished->length;

        $inkKgPerRoll   = (float) $yieldRule->ink_kg_per_m2 * $rollAreaM2;
        $inkCostPerRoll = $inkKgPerRoll * $yieldRule->inkMaterial->price;
        $cost           = $inkCostPerRoll * $this->quantity;

        return [
            'cost' => $cost,
            'breakdown' => [
                'material'        => $yieldRule->inkMaterial->name,
                'roll_area_m2'    => round($rollAreaM2, 6),
                'ink_kg_per_m2'   => (float) $yieldRule->ink_kg_per_m2,
                'ink_kg_per_roll' => round($inkKgPerRoll, 6),
                'unit_price'      => $yieldRule->inkMaterial->price,
                'cost'            => $cost,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Non-geometric BOM items (machine, tube, graphic, other)
    // -------------------------------------------------------------------------

    protected function calculateComponentCosts(): array
    {
        $cost = 0.0;
        $breakdown = [];
        $machineBreakdown = [];

        foreach ($this->bom->items as $item) {
            if (in_array($item->role->value, self::GEOMETRIC_ROLES)) {
                continue;
            }

            $effectiveQty = $item->quantity * $this->quantity
                * (1 + (($item->waste_factor_pct ?? 0) / 100));

            $unitPrice = $item->componentMaterial->price;
            $itemCost  = $effectiveQty * $unitPrice;
            $cost     += $itemCost;

            $row = [
                'material'   => $item->componentMaterial->name,
                'role'       => $item->role->value,
                'quantity'   => $effectiveQty,
                'unit_price' => $unitPrice,
                'cost'       => $itemCost,
            ];

            if ($item->role->value === 'machine') {
                $machineBreakdown[] = $row;
            } else {
                $breakdown[] = $row;
            }
        }

        return [
            'cost'              => $cost,
            'breakdown'         => $breakdown,
            'machine_breakdown' => $machineBreakdown,
        ];
    }

    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    protected function calculateSetupCost(): float
    {
        if ($this->bom->setup_time_minutes <= 0) {
            return 0.0;
        }

        $machineItem = $this->bom->items->first(
            fn ($i) => $i->role->value === 'machine'
        );

        if (! $machineItem) {
            return 0.0;
        }

        return $this->bom->setup_time_minutes * ($machineItem->componentMaterial->price / 60);
    }

    // -------------------------------------------------------------------------
    // Packaging (from yield rule)
    // -------------------------------------------------------------------------

    protected function calculatePackagingCost(mixed $yieldRule): array
    {
        if (! $yieldRule) {
            return ['cost' => 0.0, 'quantities' => [], 'breakdown' => []];
        }

        $cost = 0.0;
        $breakdown = [];
        $foilPacks = 0;

        // Inner packaging (foil wraps)
        if ($yieldRule->foil_per_roll > 0 && $yieldRule->foilMaterial) {
            $foilPacks = (int) ceil($this->quantity / $yieldRule->foil_per_roll);
            $foilCost  = $foilPacks * $yieldRule->foilMaterial->price;
            $cost     += $foilCost;

            $breakdown[] = [
                'material'   => $yieldRule->foilMaterial->name,
                'quantity'   => $foilPacks,
                'unit_price' => $yieldRule->foilMaterial->price,
                'cost'       => $foilCost,
            ];
        }

        // Outer packaging (boxes)
        $boxPacks = 0;
        if ($yieldRule->rolls_per_box > 0 && $yieldRule->boxMaterial) {
            $unitsForBox = $foilPacks > 0 ? $foilPacks : $this->quantity;
            $boxPacks    = (int) ceil($unitsForBox / $yieldRule->rolls_per_box);
            $boxCost     = $boxPacks * $yieldRule->boxMaterial->price;
            $cost       += $boxCost;

            $breakdown[] = [
                'material'   => $yieldRule->boxMaterial->name,
                'quantity'   => $boxPacks,
                'unit_price' => $yieldRule->boxMaterial->price,
                'cost'       => $boxCost,
            ];
        }

        return [
            'cost'       => $cost,
            'quantities' => [
                'foil_packs' => $foilPacks,
                'box_packs'  => $boxPacks,
            ],
            'breakdown' => $breakdown,
        ];
    }
}
