<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\Unit;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::insert([
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'RON'],
        ]);

        Unit::insert([
            ['code' => 'kg', 'name' => 'Kilogram'],
            ['code' => 'm', 'name' => 'Meter'],
            ['code' => 'pcs', 'name' => 'Pieces'],
            ['code' => 'l', 'name' => 'Litre'],
            ['code' => 'box', 'name' => 'Box'],
            ['code' => 'pack', 'name' => 'Pack'],
            ['code' => 'sqm', 'name' => 'Square Metre'],
        ]);
    }
}
