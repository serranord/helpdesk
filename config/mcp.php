<?php

return [
    // Public clients still require PKCE and the user's explicit authorization.
    // ValidateMcpOAuthRequest limits registration to HTTPS callback URLs.
    'redirect_domains' => array_filter(array_map('trim', explode(',', env('MCP_REDIRECT_DOMAINS', '*')))),
    'custom_schemes' => [],
    'authorization_server' => env('APP_URL'),
];
