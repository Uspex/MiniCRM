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
        'user'       => 'Employee',
        'activity'   => 'Work Type',
        'status'     => 'Status',
        'date'       => 'Period',
        'shift'      => 'Shift',
        'department' => 'Department',
    ],

    'status' => [
        'active'   => 'Completed',
        'inactive' => 'Not Completed',
    ],

    'history' => [
        'title'   => 'Change history',
        'date'    => 'Date',
        'editor'  => 'Changed by',
        'changes' => 'Changes',
        'from'    => 'was',
        'to'      => 'now',
        'empty'   => '—',
    ],

    'cancel' => [
        'section_title'   => 'Operations — cancellation',
        'request_btn'     => 'Request cancellation',
        'request_title'   => 'Operation cancellation request',
        'reason'          => 'Cancellation reason',
        'reason_required' => 'Please provide a cancellation reason',
        'send_request'    => 'Send request',
        'approve'         => 'Approve cancellation',
        'reject'          => 'Reject',
        'approve_title'   => 'Approve operation cancellation?',
        'reject_title'    => 'Reject the cancellation request?',
        'comment'         => 'Comment',
        'comment_hint'    => 'Optional comment on the decision',

        'requested_by'    => 'Requested by',
        'requested_at'    => 'Requested at',
        'processed_by'    => 'Processed by',
        'processed_at'    => 'Decision date',

        'status' => [
            'requested' => 'Pending cancellation',
            'cancelled' => 'Cancelled',
            'rejected'  => 'Rejected',
        ],

        'empty'           => 'No cancellation requests',
        'filter_status'   => 'Cancellation status',
        'locked_notice'   => 'This operation is being cancelled or already cancelled — editing is unavailable.',

        'request_success' => 'Cancellation request sent',
        'approve_success' => 'Operation cancellation approved',
        'reject_success'  => 'Cancellation request rejected',
        'cannot_self'     => 'You cannot approve your own cancellation request',
        'already_processed' => 'The request has already been processed',
        'not_requestable' => 'Cancellation cannot be requested for this operation',
    ],
];
