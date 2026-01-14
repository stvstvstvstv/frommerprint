<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'code',
        'name',
        'material_type',
        'unit_id',
        'active',
        'price',
        'currency_id',
        'currency',
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
}
