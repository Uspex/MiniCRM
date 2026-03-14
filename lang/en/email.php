<?php

return [

    'verify' => [
        'greeting' => 'Hello',
        'subject' => 'Verify your Email for :name',
        'line' => 'Click the button below to verify your email address.',
        'action' => 'Verify Email',
        'footer_info' => 'If you\'re having trouble clicking the ":btn_action" button, copy and paste the URL below into your web browser:',
        'signature' => ':name service',
    ],
    'reset_password' => [
        'subject'   => 'Password Reset Notification - :name',
        'title'   => ':name password reset',
        'action'    => 'Reset Password',
        'h1'  => 'Reset',
        'h2'  => 'your password?',
        'text_1'  => 'Follow the link to reset your password',
        'text_2'  => 'Information',
        'text_3'    => '*** If you did not request a password reset, no further action is required.',
    ],

    'rent_ended' => [
        'subject'   => 'Rental expiration notice for :domain',
        'title'   => 'Rental expiration notice for :domain',
        'action'    => 'Renew rental',
        'text_1'    => 'The rental for :domain is expiring.',
    ],

];
