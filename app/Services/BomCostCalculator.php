<?php

namespace App\Services;

use App\Models\Bom;

class BomCostCalculator
{
    protected Bom $bom;
    protected int $quantity;

    public function __construct(Bom $bom, int $quantity)
    {
        $this->bom = $bom;
        $this->quantity = $quantity;
    }

    public function calculate(): array
    {
        $materialCost = 0.0;
        $machineCost = 0.0;
        $materialBreakdown = [];
        $machineBreakdown = [];

        foreach ($this->bom->items as $item) {

            $baseQty = $item->quantity * $this->quantity;
            //$wasteFactor = $item->waste_factor_pct ?? 0;
            $wasteFactor = $item->role->value === 'paper'
                ? $this->paperWasteFactor()
                : ($item->waste_factor_pct ?? 0);
            $effectiveQty = $baseQty * (1 + $wasteFactor / 100);

            $unitPrice = $item->componentMaterial->price;
            $cost = $effectiveQty * $unitPrice;

            if ($item->role->value === 'machine') {
                $machineCost += $cost;
                $machineBreakdown[] = [
                    'machine' => $item->componentMaterial->name,
                    'time' => $effectiveQty,
                    'unit_price' => $unitPrice,
                    'cost' => $cost,
                ];
            } else {
                $materialCost += $cost;
                $materialBreakdown[] = [
                    'material' => $item->componentMaterial->name,
                    'role' => $item->role->value,
                    'quantity' => $effectiveQty,
                    'unit_price' => $unitPrice,
                    'cost' => $cost,
                ];
            }
        }

        $setupCost = $this->calculateSetupCost();
        $packaging = $this->calculatePackagingCost();

        $totalCost = $materialCost + $machineCost + $setupCost + $packaging['cost'];

        return [
            'unit_cost' => $totalCost / $this->quantity,
            'total_cost' => $totalCost,

            'breakdown' => [
                'materials' => $materialBreakdown,
                'machine' => $machineBreakdown,
                'setup' => $setupCost,
                'packaging' => $packaging['breakdown'],
            ],

            'packaging_quantities' => $packaging['quantities'],
        ];
    }

    protected function calculateSetupCost(): float
    {
        if ($this->bom->setup_time_minutes <= 0) {
            return 0.0;
        }

        $machineItem = $this->bom->items->firstWhere('role.value', 'machine');

        if (! $machineItem) {
            return 0.0;
        }

        $machineRatePerMinute = $machineItem->componentMaterial->price / 60;

        return $this->bom->setup_time_minutes * $machineRatePerMinute;
    }

    protected function calculatePackagingCost(): array
    {
        $rule = $this->bom->packagingRule;

        if (! $rule) {
            return [
                'cost' => 0.0,
                'quantities' => [],
                'breakdown' => [],
            ];
        }

        $innerPacks = 0;
        $outerPacks = 0;
        $cost = 0.0;
        $breakdown = [];

        // Inner packaging (foil)
        if ($rule->inner_pack_material_id && $rule->inner_pack_qty) {
            $innerPacks = (int) ceil($this->quantity / $rule->inner_pack_qty);
            $innerCost = $innerPacks * $rule->innerPackMaterial->price;

            $cost += $innerCost;
            $breakdown[] = [
                'material' => $rule->innerPackMaterial->name,
                'quantity' => $innerPacks,
                'unit_price' => $rule->innerPackMaterial->price,
                'cost' => $innerCost,
            ];
        }

        // Outer packaging (box)
        $unitsForOuter = $innerPacks > 0 ? $innerPacks : $this->quantity;
        $outerPacks = (int) ceil($unitsForOuter / $rule->outer_pack_qty);

        $outerCost = $outerPacks * $rule->outerPackMaterial->price;
        $cost += $outerCost;

        $breakdown[] = [
            'material' => $rule->outerPackMaterial->name,
            'quantity' => $outerPacks,
            'unit_price' => $rule->outerPackMaterial->price,
            'cost' => $outerCost,
        ];

        return [
            'cost' => $cost,
            'quantities' => [
                'inner_packs' => $innerPacks,
                'outer_packs' => $outerPacks,
            ],
            'breakdown' => $breakdown,
        ];
    }

    protected function paperWasteFactor(): float
    {
        $inkCount = $this->bom->items
            ->where('role.value', 'ink')
            ->count();

        return match ($inkCount) {
            1 => 1.0,
            2 => 1.5,
            3 => 2.0,
            default => 0.0,
        };
    }
}