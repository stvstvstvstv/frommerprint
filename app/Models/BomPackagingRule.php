<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomPackagingRule extends Model
{
    protected $fillable = [
        'bom_id',
        'inner_pack_material_id',
        'inner_pack_qty',
        'outer_pack_material_id',
        'outer_pack_qty',
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

    public function innerPackMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'inner_pack_material_id');
    }

    public function outerPackMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'outer_pack_material_id');
    }
}
