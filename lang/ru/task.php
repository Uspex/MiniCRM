<?php

return [
    'title' => 'Задачи',
    'add'   => 'Добавить задачу',

    'list' => [
        'head' => [
            'user'              => 'Сотрудник',
            'activity'          => 'Тип работы',
            'message'           => 'Сообщение',
            'product_count'     => 'Кол-во товаров',
            'scheduled_runtime' => 'Запланированное (мин)',
            'runtime'           => 'Время выполнения (мин)',
            'status'            => 'Статус',
            'created_at'        => 'Дата',
        ],
    ],

    'form' => [
        'create_title' => 'Добавить задачу',
        'edit_title'   => 'Редактировать задачу',
        'fields'       => [
            'user_id'            => 'Сотрудник',
            'activity_id'        => 'Тип работ',
            'message'            => 'Сообщение',
            'product_count'      => 'Кол-во товаров',
            'runtime'            => 'Время выполнения (мин)',
            'status'             => 'Статус',
        ],
    ],

    'search' => [
        'user'     => 'Сотрудник',
        'activity' => 'Тип работ',
        'status'   => 'Статус',
    ],

    'status' => [
        'active'   => 'Выполнено',
        'inactive' => 'Не выполнено',
    ],
];
