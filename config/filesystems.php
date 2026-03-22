<?php

return [
    'default' => env('FILESYSTEM_DISK', 'department_shares'),
    'disks' => [
        'department_shares' => [
            'driver' => 'local',
            'root' => BASE_PATH . '/infra/file/shares',
        ],
    ],
];
