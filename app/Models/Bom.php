<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bom extends Model
{
    protected $fillable = [
        'finished_material_id',
        'setup_time_minutes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function finishedMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'finished_material_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function packagingRule(): HasOne
    {
        return $this->hasOne(BomPackagingRule::class);
    }
}
