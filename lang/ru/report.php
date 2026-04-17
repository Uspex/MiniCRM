<?php

return [
    'title'          => 'Отчёты',
    'period'         => 'Период',
    'generate'       => 'Сформировать отчёт',
    'generate_success' => 'Отчёт успешно сформирован',
    'generate_queued'  => 'Отчёт поставлен в очередь на формирование',
    'download'         => 'Скачать',
    'download_error'   => 'Скачать лог ошибки',
    'download_name'  => 'Отчёт',
    'empty'          => 'Отчётов пока нет',
    'file_not_found' => 'Файл отчёта не найден',
    'confirm_delete' => 'Удалить отчёт?',
    'limit_message'  => 'Хранятся последние :count отчётов. Старые удаляются автоматически.',

    'status' => [
        'pending'    => 'В очереди',
        'processing' => 'Формируется',
        'completed'  => 'Готов',
        'failed'     => 'Ошибка',
    ],

    'list' => [
        'period'     => 'Период',
        'status'     => 'Статус',
        'type'       => 'Тип',
        'created_by' => 'Создал',
        'created_at' => 'Дата создания',
    ],

    'form' => [
        'fields' => [
            'type' => 'Тип отчёта',
        ],
    ],

    'type' => [
        'coefficient'  => 'Коэффициент',
        'productivity' => 'Производительность',
        'operations'   => 'Операции',
    ],

    'csv' => [
        'employee'      => 'Сотрудник',
        'average'       => 'Среднее значение',
        'total'         => 'Итого',
        'activity'      => 'Тип работ',
        'created_at'    => 'Дата создания',
        'work_day'      => 'Рабочий день',
        'shift'         => 'Смена',
        'department'    => 'Подразделение',
        'product_count' => 'Кол-во',
        'runtime'       => 'Время (ч)',
        'message'       => 'Сообщение',
    ],
];
