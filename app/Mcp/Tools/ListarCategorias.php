<?php

namespace App\Mcp\Tools;

use App\Http\Controllers\Api\TicketApiController;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListarCategorias extends ApiTool
{
    protected string $name = 'listar_categorias';

    protected string $description = 'Lista categorías activas y sus identificadores para clasificar un ticket antes de crearlo.';

    public function handle(TicketApiController $api): Response
    {
        return $this->fromApi($api->categorias());
    }
}
