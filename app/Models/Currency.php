<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol'];

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'currency', 'code');
    }

    public function latestExchangeRate()
    {
        return $this->hasOne(ExchangeRate::class)
            ->latestOfMany('rate_date');
    }
}
