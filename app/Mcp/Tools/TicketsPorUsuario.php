<?php

namespace App\Mcp\Tools;

use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class TicketsPorUsuario extends ApiTool
{
    protected string $name = 'tickets_por_usuario';

    protected string $description = 'Devuelve hasta cinco tickets recientes no cerrados del solicitante identificado por correo.';

    public function schema(JsonSchema $schema): array
    {
        return ['correo' => $schema->string()->description('Correo del solicitante.')->required()];
    }

    public function handle(Request $request, TicketApiController $api): Response
    {
        $data = $request->validate(['correo' => 'required|email|max:255']);

        return $this->fromApi($api->porUsuario($data['correo']));
    }
}
