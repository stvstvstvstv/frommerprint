<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_id',
        'rate_date',
        'rate_to_base',
    ];

        protected $casts = [
        'rate_date' => 'date',
    ];


    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
