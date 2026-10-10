<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;


class Material extends Model
{
    protected $fillable = [
        'saga_id',
        'code',
        'name',
        'material_type',
        'unit_id',
        'active',
        'width',
        'length',
        'gsm',
        'diameter',
        'price',
        'currency_id',
        'pricing_unit_id',
        'pricing_per',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function pricingUnit()
    {
        return $this->belongsTo(Unit::class, 'pricing_unit_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function bomAsFinished(): HasOne
    {
        return $this->hasOne(Bom::class, 'finished_material_id');
    }
}
