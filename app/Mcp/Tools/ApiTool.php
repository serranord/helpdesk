<?php

namespace App\Mcp\Tools;

use Illuminate\Http\JsonResponse;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

abstract class ApiTool extends Tool
{
    protected function fromApi(JsonResponse $response): Response
    {
        return $response->isSuccessful()
            ? Response::text($response->getContent())
            : Response::error($response->getContent());
    }
}
