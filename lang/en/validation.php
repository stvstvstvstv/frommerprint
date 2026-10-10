<?php

/*
| Laravel's default English messages are merged in from the framework;
| this file only adds readable names for the app's validated fields
| (Livewire property names and database column names).
*/

return [

    'attributes' => [
        // Production orders
        'finishedMaterialId'   => 'finished product',
        'finished_material_id' => 'finished product',
        'jumboMaterialId'      => 'jumbo roll',
        'plannedRolls'         => 'planned quantity',
        'client'               => 'client',
        'overrideInkKgPerM2'   => 'ink kg / m²',
        'overrideFoilPerRoll'  => 'rolls per foil wrap',
        'overrideRollsPerBox'  => 'rolls per box',

        // Materials
        'code'          => 'code',
        'saga_id'       => 'Saga ID',
        'name'          => 'name',
        'material_type' => 'material type',
        'unit_id'       => 'unit',
        'width'         => 'width',
        'length'        => 'length',
        'gsm'           => 'gsm',
        'diameter'      => 'diameter',
        'price'         => 'price',
        'currencyId'    => 'currency',
        'currency_id'   => 'currency',
        'pricingUnitId' => 'pricing unit',
        'pricingPer'    => 'priced per',
        'active'        => 'active',

        // BOM items
        'items.*.role'                  => 'role',
        'items.*.quantity'              => 'quantity',
        'items.*.waste_factor_pct'      => 'waste (%)',
        'items.*.notes'                 => 'notes',
        'newItem.component_material_id' => 'component',
        'newItem.role'                  => 'role',
        'newItem.quantity'              => 'quantity',
        'newItem.waste_factor_pct'      => 'waste (%)',
        'newItem.notes'                 => 'notes',

        // Users
        'email'    => 'email',
        'password' => 'password',
        'locale'   => 'language',

        // Exchange rates
        'rate'         => 'rate',
        'rateDate'     => 'rate date',
        'rate_date'    => 'rate date',
        'rate_to_base' => 'rate to RON',
    ],

];
