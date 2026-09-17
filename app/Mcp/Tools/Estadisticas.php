<?php

namespace App\Mcp\Tools;

use App\Http\Controllers\Api\TicketApiController;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class Estadisticas extends ApiTool
{
    protected string $name = 'estadisticas';

    protected string $description = 'Obtiene totales generales de tickets nuevos, en proceso, pendientes, críticos, resueltos hoy y SLA vencidos.';

    public function handle(TicketApiController $api): Response
    {
        return $this->fromApi($api->estadisticas());
    }
}
