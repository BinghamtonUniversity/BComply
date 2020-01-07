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
            // There should be a symlink from storage to public/storage!
            // Local Example:   ln -s storage/app/public public/storage #(or run php artisan storage:link)
            // EFS Example:     ln -sf /efs/public /var/app/current/public/storage
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
    ],
];
