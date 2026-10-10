<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Material;

/**
 * Calculates the cost of producing $quantity finished rolls from a BOM.
 *
 * Paper and ink costs are derived geometrically from the yield rule and
 * material dimensions. BOM items cover only non-geometric components
 * (machine time, graphic artwork, misc).
 *
 * All costs are in the base currency (RON). Material prices are normalised
 * by unitCost(): divided by pricing_per and converted with the exchange
 * rate on or before the costing date. Anything that can't be priced is left
 * out of the total and reported in 'problems'.
 */
class BomCostCalculator
{
    // Roles whose cost is driven by the yield rule, not BOM item quantities.
    // BOM items with these roles are skipped so they aren't counted twice.
    public const GEOMETRIC_ROLES = ['paper', 'ink', 'jumbo', 'tube', 'foil', 'box'];

    // Ink consumption is entered in kg/m², so ink must be priced per kg.
    public const INK_PRICING_UNIT = 'kg';

    protected Bom $bom;
    protected int $quantity;
    protected CurrencyConverter $converter;

    /** @var array<string, array{code: string, material?: string, currency?: string, unit?: string}> */
    protected array $problems = [];

    public function __construct(Bom $bom, int $quantity, ?CurrencyConverter $converter = null)
    {
        $this->bom = $bom;
        $this->quantity = $quantity;
        $this->converter = $converter ?? new CurrencyConverter();
    }

    public function calculate(): array
    {
        $yieldRule  = $this->bom->yieldRule;
        $finished   = $this->bom->finishedMaterial;

        $paper      = $this->calculatePaperCost($yieldRule, $finished);
        $ink        = $this->calculateInkCost($yieldRule, $finished);
        $tube       = $this->calculateTubeCost($yieldRule);
        $components = $this->calculateComponentCosts();
        $setup      = $this->calculateSetupCost();
        $packaging  = $this->calculatePackagingCost($yieldRule);

        $totalCost = $paper['cost']
            + $ink['cost']
            + $tube['cost']
            + $components['cost']
            + $setup
            + $packaging['cost'];

        return [
            'unit_cost'  => $totalCost / $this->quantity,
            'total_cost' => $totalCost,

            'breakdown' => [
                'paper'      => $paper['breakdown'],
                'ink'        => $ink['breakdown'],
                'tube'       => $tube['breakdown'],
                'components' => $components['breakdown'],
                'machine'    => $components['machine_breakdown'],
                'setup'      => $setup,
                'packaging'  => $packaging['breakdown'],
            ],

            'packaging_quantities' => $packaging['quantities'],

            'currency'   => CurrencyConverter::baseCode(),
            'price_date' => $this->converter->date()->toDateString(),

            // Materials left out of the total because they couldn't be priced
            'problems'   => array_values($this->problems),
        ];
    }

    // -------------------------------------------------------------------------
    // Prices
    // -------------------------------------------------------------------------

    /**
     * Cost of one pricing unit of the material (e.g. one m², kg, piece or
     * hour) in the base currency, or null if it can't be priced, in which
     * case the reason is recorded in $this->problems.
     */
    protected function unitCost(Material $material, ?string $requiredPricingUnit = null): ?float
    {
        if ($requiredPricingUnit !== null && $material->pricingUnit?->code !== $requiredPricingUnit) {
            return $this->problem('wrong_pricing_unit', $material, [
                'unit'     => $material->pricingUnit?->code ?? '—',
                'required' => $requiredPricingUnit,
            ]);
        }

        if ($material->price === null) {
            return $this->problem('missing_price', $material);
        }

        $perUnit = (float) $material->price / max(1, (int) $material->pricing_per);
        $cost    = $this->converter->toBase($perUnit, $material->currency);

        if ($cost === null) {
            return $this->problem('missing_rate', $material, [
                'currency' => $material->currency->code,
                'date'     => $this->converter->date()->toDateString(),
            ]);
        }

        return $cost;
    }

    protected function problem(string $code, Material $material, array $details = []): null
    {
        $this->problems[$code . ':' . $material->id] = ['code' => $code, 'material' => $material->name] + $details;

        return null;
    }

    // -------------------------------------------------------------------------
    // Paper (geometric)
    // -------------------------------------------------------------------------

    /**
     * Why paper cost can't be calculated for this finished roll and jumbo,
     * or null when it can. Production orders use this to refuse saving.
     *
     * @return array{code: 'missing_dimensions'|'jumbo_too_narrow', missing: list<string>}|null
     */
    public static function paperProblem(mixed $finished, mixed $jumbo): ?array
    {
        $missing = [];
        if (! ($finished->width > 0))  $missing[] = 'finished_width';
        if (! ($finished->length > 0)) $missing[] = 'finished_length';
        if (! ($jumbo->width > 0))     $missing[] = 'jumbo_width';

        if ($missing) {
            return ['code' => 'missing_dimensions', 'missing' => $missing];
        }

        // Not even one finished-width strip fits across the jumbo
        if ($jumbo->width < $finished->width) {
            return ['code' => 'jumbo_too_narrow', 'missing' => []];
        }

        return null;
    }

    protected function calculatePaperCost(mixed $yieldRule, mixed $finished): array
    {
        if (! $yieldRule?->preferredJumboMaterial) {
            return ['cost' => 0.0, 'breakdown' => null];
        }

        $jumbo = $yieldRule->preferredJumboMaterial;

        if ($problem = self::paperProblem($finished, $jumbo)) {
            return ['cost' => 0.0, 'breakdown' => [
                'error'             => $problem['code'],
                'missing'           => $problem['missing'],
                'jumbo'             => $jumbo->name,
                'jumbo_width_mm'    => $jumbo->width,
                'finished_width_mm' => $finished->width,
            ]];
        }

        // Rolls cut per shaft pass (how many finished-width strips fit across the jumbo)
        $rollsPerShaft = (int) floor($jumbo->width / $finished->width);

        // Number of shaft passes required for the full order
        $shafts = (int) ceil($this->quantity / $rollsPerShaft);

        // Paper area consumed (m²): shafts × jumbo_width_m × roll_length_m
        $jumboWidthM  = $jumbo->width / 1000;
        $grossAreaM2  = $shafts * $jumboWidthM * $finished->length;

        // Apply production waste
        $wastePct    = (float) ($yieldRule->paper_waste_pct ?? 1.0);
        $netAreaM2   = $grossAreaM2 * (1 + $wastePct / 100);

        // Jumbo is priced per m²
        $pricePerM2  = $this->unitCost($jumbo);
        $cost        = $netAreaM2 * ($pricePerM2 ?? 0.0);

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
        $pricePerKg     = $this->unitCost($yieldRule->inkMaterial, self::INK_PRICING_UNIT);
        $cost           = $inkKgPerRoll * ($pricePerKg ?? 0.0) * $this->quantity;

        return [
            'cost' => $cost,
            'breakdown' => [
                'material'        => $yieldRule->inkMaterial->name,
                'roll_area_m2'    => round($rollAreaM2, 6),
                'ink_kg_per_m2'   => (float) $yieldRule->ink_kg_per_m2,
                'ink_kg_per_roll' => round($inkKgPerRoll, 6),
                'unit_price'      => $pricePerKg,
                'cost'            => $cost,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Tube (1 per roll, from yield rule)
    // -------------------------------------------------------------------------

    protected function calculateTubeCost(mixed $yieldRule): array
    {
        if (! $yieldRule?->tubeMaterial) {
            return ['cost' => 0.0, 'breakdown' => null];
        }

        $unitPrice = $this->unitCost($yieldRule->tubeMaterial);
        $cost      = ($unitPrice ?? 0.0) * $this->quantity;

        return [
            'cost' => $cost,
            'breakdown' => [
                'material'   => $yieldRule->tubeMaterial->name,
                'quantity'   => $this->quantity,
                'unit_price' => $unitPrice,
                'cost'       => $cost,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Non-geometric BOM items (machine, graphic, other)
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

            $unitPrice = $this->unitCost($item->componentMaterial);
            $itemCost  = $effectiveQty * ($unitPrice ?? 0.0);
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

        // Machine rate is per hour
        $hourlyRate = $this->unitCost($machineItem->componentMaterial) ?? 0.0;

        return $this->bom->setup_time_minutes * ($hourlyRate / 60);
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
            $foilPrice = $this->unitCost($yieldRule->foilMaterial);
            $foilCost  = $foilPacks * ($foilPrice ?? 0.0);
            $cost     += $foilCost;

            $breakdown[] = [
                'material'   => $yieldRule->foilMaterial->name,
                'quantity'   => $foilPacks,
                'unit_price' => $foilPrice,
                'cost'       => $foilCost,
            ];
        }

        // Outer packaging (boxes)
        $boxPacks = 0;
        if ($yieldRule->rolls_per_box > 0 && $yieldRule->boxMaterial) {
            $unitsForBox = $foilPacks > 0 ? $foilPacks : $this->quantity;
            $boxPacks    = (int) ceil($unitsForBox / $yieldRule->rolls_per_box);
            $boxPrice    = $this->unitCost($yieldRule->boxMaterial);
            $boxCost     = $boxPacks * ($boxPrice ?? 0.0);
            $cost       += $boxCost;

            $breakdown[] = [
                'material'   => $yieldRule->boxMaterial->name,
                'quantity'   => $boxPacks,
                'unit_price' => $boxPrice,
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
