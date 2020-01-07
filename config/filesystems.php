<?php

return [
    'default' => env('FILESYSTEM_DRIVER', 'local'),
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => env('FILE_STORAGE_PATH',storage_path('app')),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
    ],
];
