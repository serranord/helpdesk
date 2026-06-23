<?php
namespace App\Http\Controllers;
use App\Models\Ticket; use App\Models\Calificacion; use App\Models\ActividadLog;
use Illuminate\Http\Request;

class CalificacionController extends Controller {
    public function store(Request $request, Ticket $ticket) {
        $user = auth()->user();
        if ($ticket->solicitante_id !== $user->id) abort(403);
        if ($ticket->estado !== 'resuelto') abort(403, 'Solo puedes calificar tickets resueltos.');
        if ($ticket->calificacion) return back()->with('error', 'Ya calificaste este ticket.');

        $request->validate([
            'estrellas'  => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        Calificacion::create([
            'ticket_id'  => $ticket->id,
            'usuario_id' => $user->id,
            'estrellas'  => $request->estrellas,
            'comentario' => $request->comentario,
        ]);

        ActividadLog::registrar('calificó', 'tickets', "Calificó ticket {$ticket->numero} con {$request->estrellas} estrellas", $ticket->numero);
        return back()->with('success', '¡Gracias por tu calificación!');
    }
}
