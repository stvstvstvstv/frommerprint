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
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
