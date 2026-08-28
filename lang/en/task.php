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
        'decision' => 'Decision',
        'changes' => 'Changes / Comment',
        'event'   => 'Event',
        'from'    => 'was',
        'to'      => 'now',
        'empty'   => '—',

        'events' => [
            'updated'          => 'Edit',
            'edit_requested'   => 'Edit sent for approval',
            'edit_approved'    => 'Edit approved',
            'edit_rejected'    => 'Edit rejected',
            'cancel_requested' => 'Cancellation requested',
            'cancel_approved'  => 'Cancellation approved',
            'cancel_rejected'  => 'Cancellation rejected',
        ],
    ],

    'cancel' => [
        'request_btn'     => 'Request cancellation',
        'request_title'   => 'Operation cancellation request',
        'reason'          => 'Cancellation reason',
        'reason_required' => 'Please provide a cancellation reason',
        'send_request'    => 'Send request',
        'request_success' => 'Cancellation request sent',
    ],


    'request' => [
        'section_title'        => 'Operations — requests',
        'type_title'           => 'Type',
        'operation'            => 'Operation',
        'details'              => 'Details',
        'submit'               => 'Send for approval',
        'submit_hint'          => 'Changes are applied after approval',
        'edit_sent'            => 'Edit sent for approval',
        'no_changes'           => 'Nothing changed — there is nothing to approve',
        'pending_notice'       => 'The operation has a request awaiting approval — changes are unavailable until it is processed.',
        'cancelled_notice'     => 'The operation is cancelled — changes are unavailable.',
        'no_permission_notice' => 'No permission to edit the operation — the data is read-only.',
        'reason'               => 'Reason',
        'reason_hint'          => 'Optional comment on the request',
        'requested_by'         => 'Requested by',
        'processed_by'         => 'Processed by',
        'comment'              => 'Comment',
        'comment_hint'         => 'Optional comment on the decision',
        'approve'              => 'Approve',
        'reject'               => 'Reject',
        'approve_title'        => 'Approve the request?',
        'reject_title'         => 'Reject the request?',
        'approve_success'      => 'Request approved',
        'reject_success'       => 'Request rejected',
        'already_processed'    => 'The request has already been processed',
        'cannot_self'          => 'You cannot approve your own request',
        'empty'                => 'No requests',
        'filter_type'          => 'Request type',
        'filter_status'        => 'Request status',

        'type' => [
            'cancel' => 'Cancellation',
            'edit'   => 'Edit',
        ],

        'status' => [
            'requested' => 'Awaiting decision',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
        ],
    ],
];
