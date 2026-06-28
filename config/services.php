<?php
return [
    'teams' => [
        'webhook_url' => env('TEAMS_WEBHOOK_URL', ''),
    ],

    'microsoft' => [
        'tenant_id'     => env('AZURE_TENANT_ID'),
        'client_id'     => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect'      => env('AZURE_REDIRECT_URI', '/auth/microsoft/callback'),
    ],
];
