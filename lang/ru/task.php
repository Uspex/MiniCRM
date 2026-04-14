<?php

return [
    'title' => 'Операции',
    'add'   => 'Добавить операцию',

    'list' => [
        'head' => [
            'user'              => 'Сотрудник',
            'activity'          => 'Тип работы',
            'message'           => 'Сообщение',
            'product_count'     => 'Кол-во',
            'scheduled_runtime' => 'Запланированное (мин)',
            'runtime'           => 'Время выполнения (ч)',
            'status'            => 'Статус',
            'shift'             => 'Смена',
            'work_day'          => 'Рабочий день',
            'created_at'        => 'Дата',
        ],
    ],

    'form' => [
        'create_title' => 'Добавить операцию',
        'edit_title'   => 'Редактировать операцию',
        'fields'       => [
            'user_id'            => 'Сотрудник',
            'activity_id'        => 'Тип работ',
            'message'            => 'Сообщение',
            'product_count'      => 'Кол-во',
            'runtime'            => 'Время выполнения (ч)',
            'status'             => 'Статус',
            'shift'              => 'Смена',
            'work_day'           => 'Рабочий день',
        ],
    ],

    'search' => [
        'user'     => 'Сотрудник',
        'activity' => 'Тип работ',
        'status'   => 'Статус',
        'date'     => 'Период',
        'shift'    => 'Смена',
    ],

    'status' => [
        'active'   => 'Выполнено',
        'inactive' => 'Не выполнено',
    ],
];
