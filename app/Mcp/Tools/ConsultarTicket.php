<?php

namespace App\Mcp\Tools;

use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ConsultarTicket extends ApiTool
{
    protected string $name = 'consultar_ticket';

    protected string $description = 'Consulta estado, SLA y última actualización pública de un ticket por número.';

    public function schema(JsonSchema $schema): array
    {
        return ['numero' => $schema->string()->description('Número de ticket, por ejemplo TKT-00025.')->required()];
    }

    public function handle(Request $request, TicketApiController $api): Response
    {
        $data = $request->validate(['numero' => 'required|string|max:50']);

        return $this->fromApi($api->porNumero(trim($data['numero'])));
    }
}
