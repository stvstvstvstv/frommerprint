<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialStock extends Model
{
    protected $fillable = [
        'material_id',
        'qty_on_hand',
        'qty_reserved',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
