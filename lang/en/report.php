<?php

return [
    'title'          => 'Reports',
    'period'         => 'Period',
    'generate'       => 'Generate Report',
    'generate_success' => 'Report generated successfully',
    'generate_queued'  => 'Report has been queued for generation',
    'download'         => 'Download',
    'download_error'   => 'Download error log',
    'download_name'  => 'Report',
    'empty'          => 'No reports yet',
    'file_not_found' => 'Report file not found',
    'confirm_delete' => 'Delete report?',
    'limit_message'  => 'Only the last :count reports are stored. Older reports are deleted automatically.',

    'status' => [
        'pending'    => 'Queued',
        'processing' => 'Processing',
        'completed'  => 'Completed',
        'failed'     => 'Failed',
    ],

    'list' => [
        'period'     => 'Period',
        'status'     => 'Status',
        'type'       => 'Type',
        'created_by' => 'Created by',
        'created_at' => 'Created at',
    ],

    'form' => [
        'fields' => [
            'type' => 'Report type',
        ],
    ],

    'type' => [
        'coefficient'         => 'Coefficient',
        'productivity'        => 'Productivity',
        'operations'          => 'Operations',
        'operations_history'  => 'Operation change history',
        'operations_cancelled' => 'Cancelled operations',
    ],

    'csv' => [
        'employee'      => 'Employee',
        'total'         => 'Total',
        'total_average' => 'Total Average',
        'activity'      => 'Activity',
        'created_at'    => 'Created at',
        'work_day'      => 'Work day',
        'shift'         => 'Shift',
        'department'    => 'Department',
        'product_count' => 'Quantity',
        'runtime'       => 'Time (h)',
        'message'       => 'Message',
        'changed_at'    => 'Changed at',
        'editor'        => 'Changed by',
        'operation_id'  => 'Operation ID',
        'field'         => 'Changed field',
        'old_value'     => 'Was',
        'new_value'     => 'Now',
        'cancel_reason'    => 'Cancellation reason',
        'cancel_requester' => 'Requested by',
        'cancel_requested_at' => 'Requested at',
        'cancel_approver'  => 'Approved by',
        'cancel_approved_at' => 'Approved at',
    ],
];
