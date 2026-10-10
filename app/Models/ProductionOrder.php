<?php

namespace App\Models;

use App\Services\BomCostCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrder extends Model
{
    public const STATUS_DRAFT         = 'draft';
    public const STATUS_IN_PRODUCTION = 'in_production';
    public const STATUS_COMPLETE      = 'complete';
    public const STATUS_CANCELLED     = 'cancelled';

    protected $fillable = [
        'finished_material_id',
        'jumbo_material_id',
        'planned_rolls',
        'client',

        'snapshot_finished_width_mm',
        'snapshot_finished_length_m',
        'snapshot_jumbo_width_mm',
        'snapshot_jumbo_gsm',
        'snapshot_jumbo_cost_per_m',

        'override_ink_kg_per_m2',
        'override_foil_per_roll',
        'override_rolls_per_box',

        'status',
        'confirmed_total_cost',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'planned_rolls'              => 'integer',
        'snapshot_finished_width_mm' => 'decimal:2',
        'snapshot_finished_length_m' => 'decimal:3',
        'snapshot_jumbo_width_mm'    => 'decimal:2',
        'snapshot_jumbo_gsm'         => 'decimal:2',
        'snapshot_jumbo_cost_per_m'  => 'decimal:6',
        'override_ink_kg_per_m2'     => 'decimal:6',
        'override_foil_per_roll'     => 'integer',
        'override_rolls_per_box'     => 'integer',
        'confirmed_total_cost'       => 'decimal:4',
        'started_at'                 => 'datetime',
        'completed_at'               => 'datetime',
    ];

    public function finishedMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'finished_material_id');
    }

    public function jumboMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'jumbo_material_id');
    }

    public function isDraft(): bool        { return $this->status === self::STATUS_DRAFT; }
    public function isInProduction(): bool { return $this->status === self::STATUS_IN_PRODUCTION; }
    public function isComplete(): bool     { return $this->status === self::STATUS_COMPLETE; }
    public function isCancelled(): bool    { return $this->status === self::STATUS_CANCELLED; }

    /**
     * Translated reasons an order can't be costed for paper (missing
     * dimensions, or a jumbo narrower than the roll), keyed by the side at
     * fault: 'finished' and/or 'jumbo'. Empty when paper cost can be
     * calculated. Saving and starting orders is refused while non-empty.
     *
     * @return array<'finished'|'jumbo', string>
     */
    public static function paperErrors(Material $finished, Material $jumbo): array
    {
        $problem = BomCostCalculator::paperProblem($finished, $jumbo);

        if (! $problem) {
            return [];
        }

        if ($problem['code'] === 'jumbo_too_narrow') {
            return ['jumbo' => __('orders.jumbo_too_narrow', [
                'jumbo_width'    => $jumbo->width,
                'finished_width' => $finished->width,
            ])];
        }

        $missingMessage = fn (array $fields) => __('orders.missing_dimensions', [
            'fields' => collect($fields)->map(fn ($field) => __('bom.dimensions.' . $field))->join(', '),
        ]);

        $errors   = [];
        $finishedMissing = array_values(array_diff($problem['missing'], ['jumbo_width']));

        if ($finishedMissing) {
            $errors['finished'] = $missingMessage($finishedMissing);
        }
        if (in_array('jumbo_width', $problem['missing'], true)) {
            $errors['jumbo'] = $missingMessage(['jumbo_width']);
        }

        return $errors;
    }

    /**
     * Message for cost results whose 'problems' list materials that could
     * not be priced. An order with such problems can't be started, since
     * the frozen total would leave those materials out.
     */
    public static function unpricedMessage(array $problems): string
    {
        return __('orders.unpriced_materials', [
            'materials' => collect($problems)->pluck('material')->unique()->join(', '),
        ]);
    }

    public function statusLabel(): string
    {
        return __('orders.statuses.' . $this->status);
    }
}
