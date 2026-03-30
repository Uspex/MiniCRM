<?php

return [
    'title'          => 'Server',
    'btn_update'     => 'Update Server',
    'confirm_update' => 'Are you sure? This will pull code, run migrations and seeders.',
    'update_success' => 'Server updated successfully',
    'update_error'   => 'Error updating server',
    'output'         => 'Output',
    'backups_title'  => 'Database Backups',
    'backup_name'    => 'File',
    'backup_size'    => 'Size',
    'backup_date'    => 'Date',

    'steps' => [
        'backup'  => 'Database Backup',
        'git'     => 'Git pull',
        'migrate' => 'Migrations',
        'seed'    => 'Seeders',
        'cache'   => 'Cache clear',
    ],
];
