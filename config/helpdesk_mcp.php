<?php

return [
    'token' => env('MCP_TOKEN', ''),
    'allowed_origins' => array_filter(explode(',', env('MCP_ALLOWED_ORIGINS', ''))),
];
