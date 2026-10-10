<?php

namespace App\Models;

use App\Enums\BomItemRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    protected $fillable = [
        'bom_id',
        'component_material_id',
        'role',
        'quantity',
        'waste_factor_pct',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'waste_factor_pct' => 'decimal:2',
        'role' => BomItemRole::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function componentMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'component_material_id');
    }
}
