<?php

return [
    'title' => 'Tasks',
    'add'   => 'Add Task',

    'list' => [
        'head' => [
            'user'              => 'Employee',
            'activity'          => 'Work Type',
            'message'           => 'Message',
            'product_count'     => 'Count',
            'scheduled_runtime' => 'Scheduled',
            'runtime'           => 'Actual Time (h)',
            'status'            => 'Status',
            'shift'             => 'Shift',
            'work_day'          => 'Work Day',
            'created_at'        => 'Date',
        ],
    ],

    'form' => [
        'create_title' => 'Add Task',
        'edit_title'   => 'Edit Task',
        'fields'       => [
            'user_id'       => 'Employee',
            'activity_id'   => 'Work Type',
            'message'       => 'Message',
            'product_count' => 'Count',
            'runtime'       => 'Actual Time (h)',
            'status'        => 'Status',
            'shift'         => 'Shift',
            'work_day'      => 'Work Day',
        ],
    ],

    'search' => [
        'user'     => 'Employee',
        'activity' => 'Work Type',
        'status'   => 'Status',
        'date'     => 'Period',
        'shift'    => 'Shift',
    ],

    'status' => [
        'active'   => 'Completed',
        'inactive' => 'Not Completed',
    ],
];
