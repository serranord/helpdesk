<?php

namespace App\Mcp\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class BuscarTickets extends Tool
{
    protected string $name = 'buscar_tickets';

    protected string $description = 'Busca tickets por título o número y filtra por estado y prioridad. Resultados paginados, más recientes primero.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'texto' => $schema->string()->description('Texto contenido en el título o número.'),
            'estado' => $schema->string()->enum(array_keys(Ticket::estados())),
            'prioridad' => $schema->string()->enum(['baja', 'media', 'alta', 'critica']),
            'pagina' => $schema->integer()->min(1),
            'por_pagina' => $schema->integer()->min(1)->max(50),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'texto' => 'sometimes|string|max:150',
            'estado' => ['sometimes', Rule::in(array_keys(Ticket::estados()))],
            'prioridad' => 'sometimes|in:baja,media,alta,critica',
            'pagina' => 'sometimes|integer|min:1|max:100000',
            'por_pagina' => 'sometimes|integer|min:1|max:50',
        ]);

        $query = Ticket::query();
        if (isset($data['texto'])) {
            $query->where(function ($query) use ($data) {
                $query->where('titulo', 'like', '%'.$data['texto'].'%')
                    ->orWhere('numero', 'like', '%'.$data['texto'].'%');
            });
        }
        foreach (['estado', 'prioridad'] as $filter) {
            if (isset($data[$filter])) {
                $query->where($filter, $data[$filter]);
            }
        }

        $tickets = $query->orderByDesc('id')->paginate(
            $data['por_pagina'] ?? 20,
            ['id', 'numero', 'titulo', 'estado', 'prioridad', 'created_at', 'fecha_limite'],
            'pagina',
            $data['pagina'] ?? 1,
        );

        return Response::json([
            'total' => $tickets->total(),
            'pagina' => $tickets->currentPage(),
            'ultima_pagina' => $tickets->lastPage(),
            'tickets' => $tickets->items(),
        ]);
    }
}
