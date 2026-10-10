<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomYieldRule extends Model
{
    protected $fillable = [
        'bom_id',
        'preferred_jumbo_material_id',
        'paper_waste_pct',
        'ink_kg_per_m2',
        'ink_material_id',
        'tube_material_id',
        'foil_per_roll',
        'rolls_per_box',
        'foil_material_id',
        'box_material_id',
    ];

    protected $casts = [
        'paper_waste_pct' => 'decimal:2',
        'ink_kg_per_m2'   => 'decimal:6',
        'foil_per_roll'   => 'integer',
        'rolls_per_box'   => 'integer',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function preferredJumboMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'preferred_jumbo_material_id');
    }

    public function inkMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'ink_material_id');
    }

    public function tubeMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'tube_material_id');
    }

    public function foilMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'foil_material_id');
    }

    public function boxMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'box_material_id');
    }
}
