<?php

return [
    'title' => 'Work Types',
    'add'   => 'Add Work Type',

    'list' => [
        'head' => [
            'name'          => 'Name',
            'slug'          => 'Slug',
            'plan_quantity' => 'Plan Qty/Shift',
            'plan_time'     => 'Plan Time/Item (h)',
        ],
    ],

    'form' => [
        'create_title' => 'Add Work Type',
        'edit_title'   => 'Edit Work Type',
        'fields'       => [
            'name'          => 'Name',
            'slug'          => 'Slug',
            'plan_quantity' => 'Plan Quantity per Shift',
            'plan_time'     => 'Plan Time per Item (h)',
        ],
    ],

    'search' => [
        'name' => 'Name',
    ],
];
