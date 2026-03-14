<?php

return [

    'db' => [
        'status' => env('INTEGRATION_status', false),
        'schedule' => env('INTEGRATION_DB_SCHEDULE', '0 * * * *'), // Every hour
        'db_host' => env('INTEGRATION_DB_HOST'),
        'db_port' => env('INTEGRATION_DB_PORT'),
        'db_database' => env('INTEGRATION_DB_DATABASE'),
        'db_username' => env('INTEGRATION_DB_USERNAME'),
        'db_password' => env('INTEGRATION_DB_PASSWORD'),
    ],

];
