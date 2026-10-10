<?php

return [
    'title' => 'Comenzi',

    'add' => 'Adaugă comandă',
    'edit' => 'Editează comandă',
    'confirm_delete_order' => 'Șterge comandă?  Această acțiune nu poate fi anulată.',

    'created'   => 'Comandă de producție creată.',
    'updated'   => 'Comanda #:id a fost actualizată.',
    'confirmed' => 'Comanda #:id a fost trecută în producție.',
    'completed' => 'Comanda #:id a fost marcată completă.',
    'cancelled' => 'Comanda #:id a fost anulată.',

    'NoBOMYieldCreate' => 'Nu a fost găsită o producție BOM pentru acest produs. Vă rugăm să creați o producție BOM înainte de a crea o comandă pentru acest produs.',
    'NoBOMYieldEdit' => 'Nu a fost găsită o producție BOM pentru acest produs. Vă rugăm să creați o producție BOM înainte de a edita această comandă.',
    'NoBOMYieldWarning' => 'Nicio regulă de producție BOM pentru acest produs — doar costul hârtiei va fi estimat.',

    'index_title' => 'Comenzi de producție',
    'new'         => 'Comandă nouă',
    'empty'       => 'Nu există încă comenzi de producție.',
    'create_one'  => 'Creează una',

    'columns' => [
        'client'    => 'Client',
        'product'   => 'Produs',
        'jumbo'     => 'Jumbo',
        'qty'       => 'Cant.',
        'status'    => 'Stare',
        'cost'      => 'Cost',
        'unit_cost' => 'Unitar',
    ],

    'confirm_start'    => 'Treceți comanda #:id în producție? Costul va fi înghețat.',
    'confirm_complete' => 'Marcați comanda #:id ca finalizată?',
    'confirm_cancel'   => 'Anulați comanda #:id?',

    'statuses' => [
        'draft' => 'Ciornă',
        'in_production' => 'În producție',
        'complete' => 'Completă',
        'cancelled' => 'Anulată',
    ],
];