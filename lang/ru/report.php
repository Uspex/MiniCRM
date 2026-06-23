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
        'coefficient'         => 'Коэффициент',
        'productivity'        => 'Производительность',
        'operations'          => 'Операции',
        'operations_history'  => 'История изменений операций',
        'operations_cancelled' => 'Отменённые операции',
    ],

    'csv' => [
        'employee'      => 'Сотрудник',
        'total'         => 'Итого',
        'activity'      => 'Тип работ',
        'created_at'    => 'Дата создания',
        'work_day'      => 'Рабочий день',
        'shift'         => 'Смена',
        'department'    => 'Подразделение',
        'product_count' => 'Кол-во',
        'runtime'       => 'Время (ч)',
        'message'       => 'Сообщение',
        'changed_at'    => 'Дата изменения',
        'editor'        => 'Кто изменил',
        'operation_id'  => 'ID операции',
        'field'         => 'Изменённое поле',
        'old_value'     => 'Было',
        'new_value'     => 'Стало',
        'cancel_reason'    => 'Причина отмены',
        'cancel_requester' => 'Кто запросил',
        'cancel_requested_at' => 'Дата запроса',
        'cancel_approver'  => 'Кто одобрил',
        'cancel_approved_at' => 'Дата одобрения',
    ],
];
