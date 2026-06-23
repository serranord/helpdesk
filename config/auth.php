<?php
return [
    'defaults' => ['guard' => 'web', 'passwords' => 'usuarios'],
    'guards'   => [
        'web' => ['driver' => 'session', 'provider' => 'usuarios'],
    ],
    'providers' => [
        'usuarios' => ['driver' => 'eloquent', 'model' => App\Models\Usuario::class],
    ],
    'passwords' => [
        'usuarios' => ['provider' => 'usuarios', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],
    'password_timeout' => 10800,
];
