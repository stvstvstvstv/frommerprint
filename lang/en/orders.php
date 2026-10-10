<?php

return [
    'title' => 'Orders',

    'add' => 'Add order',
    'edit' => 'Edit order',
    'confirm_delete_order' => 'Delete order?  This action cannot be undone.',
    'created'   => 'Production order created.',
    'updated'   => 'Order #:id updated.',
    'confirmed' => 'Order #:id moved to In Production.',
    'completed' => 'Order #:id marked Complete.',
    'cancelled' => 'Order #:id cancelled.',

    'NoBOMYieldCreate' => 'No BOM yield found for this product. Please create a BOM yield before creating an order for this product.',
    'NoBOMYieldEdit' => 'No BOM yield found for this product. Please create a BOM yield before editing this order.',
    'NoBOMYieldWarning' => 'No BOM yield rule for this product — only paper cost will be estimated.',
    

    'index_title' => 'Production Orders',
    'new'         => 'New Order',
    'empty'       => 'No production orders yet.',
    'create_one'  => 'Create one',

    'columns' => [
        'client'    => 'Client',
        'product'   => 'Product',
        'jumbo'     => 'Jumbo',
        'qty'       => 'Qty',
        'status'    => 'Status',
        'cost'      => 'Cost',
        'unit_cost' => 'Unit',
    ],

    'confirm_start'    => 'Move order #:id to In Production? This will freeze the cost.',
    'confirm_complete' => 'Mark order #:id as Complete?',
    'confirm_cancel'   => 'Cancel order #:id?',

    'statuses' => [
        'draft' => 'Draft',
        'in_production' => 'In Production',
        'complete' => 'Complete',
        'cancelled' => 'Cancelled',
    ],

];