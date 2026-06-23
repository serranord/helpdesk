<?php
return [
    'default' => env('DB_CONNECTION', 'sqlite'),
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'url'      => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'   => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        'mysql' => [
            'driver'    => 'mysql', 'url' => env('DB_URL'),
            'host'      => env('DB_HOST','127.0.0.1'), 'port' => env('DB_PORT','3306'),
            'database'  => env('DB_DATABASE','helpdesk'), 'username' => env('DB_USERNAME','root'),
            'password'  => env('DB_PASSWORD',''), 'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true, 'engine' => null,
        ],
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],
];
