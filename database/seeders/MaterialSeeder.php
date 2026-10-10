<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'code' => 'RAW-PAPER-80',
                'name' => 'Paper 80gsm',
                'material_type' => 'raw',
                'unit_id' => 2,
                'active' => 1,
            ],
            [
                'code' => 'RAW-INK-BLK',
                'name' => 'Black Printing Ink',
                'material_type' => 'raw',
                'unit_id' => 4,
                'active' => 1,
            ],
            [
                'code' => 'PACK-BOX-S',
                'name' => 'Small Cardboard Box',
                'material_type' => 'packaging',
                'unit_id' => 3,
                'active' => 1,
            ],
            [
                'code' => 'FOLIE-SHRINK-500',
                'name' => 'Wrapping Foil 500mm',
                'material_type' => 'packaging',
                'unit_id' => 2,
                'active' => 1,
            ],
            [
                'code' => 'FIN-FLYER-A5',
                'name' => 'A5 Printed Flyer',
                'material_type' => 'finished',
                'unit_id' => 3,
                'active' => 1,
            ],
            [
                'code' => 'RAW-GLUE',
                'name' => 'Industrial Glue',
                'material_type' => 'raw',
                'unit_id' => 4,
                'active' => 0,
            ],
            [
                'code' => 'CARD-ROLL-78MM',
                'name' => 'Cardboard Roll 78mm',
                'material_type' => 'raw',
                'unit_id' => 3,
                'active' => 0,
            ],
            [
                'code' => 'PLAST-ROLL-78MM',
                'name' => 'Plastic Roll 78mm',
                'material_type' => 'raw',
                'unit_id' => 3,
                'active' => 0,
            ],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['code' => $material['code']],
                $material
            );
        }
    }
}