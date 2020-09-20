<?php

return [
    'host' => env('AMI_HOST'),
    'port' => env('AMI_PORT'),
    'username' => env('AMI_USERNAME'),
    'secret' => env('AMI_SECRET'),
    'dongle' => [
        'sms' => [
            'device' => env('AMI_DEVICE'),
        ],
    ],
];
