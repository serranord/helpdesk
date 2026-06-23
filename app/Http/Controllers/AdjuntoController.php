<?php
namespace App\Http\Controllers;
use App\Models\Ticket; use App\Models\Adjunto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdjuntoController extends Controller {

    public function store(Request $request, Ticket $ticket) {
        $user = auth()->user();
        if ($user->esSolicitante() && $ticket->solicitante_id !== $user->id) abort(403);

        $request->validate([
            'archivos'   => 'required|array|max:5',
            'archivos.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ], [
            'archivos.required'   => 'Selecciona al menos un archivo.',
            'archivos.*.max'      => 'Cada archivo no puede superar 10MB.',
            'archivos.*.mimes'    => 'Tipo de archivo no permitido.',
        ]);

        $subidos = 0;
        foreach ($request->file('archivos') as $file) {
            $nombre = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->storeAs("adjuntos/ticket-{$ticket->id}", $nombre, 'local');
            Adjunto::create([
                'ticket_id'       => $ticket->id,
                'usuario_id'      => $user->id,
                'nombre_original' => $file->getClientOriginalName(),
                'nombre_guardado' => $nombre,
                'mime_type'       => $file->getMimeType(),
                'tamano'          => $file->getSize(),
            ]);
            $subidos++;
        }

        return back()->with('success', "{$subidos} archivo(s) adjuntado(s) correctamente.");
    }

    public function download(Adjunto $adjunto) {
        $user = auth()->user();
        $ticket = $adjunto->ticket;
        if ($user->esSolicitante() && $ticket->solicitante_id !== $user->id) abort(403);

        $path = storage_path("app/adjuntos/ticket-{$ticket->id}/{$adjunto->nombre_guardado}");
        if (!file_exists($path)) abort(404, 'Archivo no encontrado.');
        return response()->download($path, $adjunto->nombre_original);
    }

    public function destroy(Adjunto $adjunto) {
        if (!auth()->user()->puedeGestionar()) abort(403);
        $path = storage_path("app/adjuntos/ticket-{$adjunto->ticket_id}/{$adjunto->nombre_guardado}");
        if (file_exists($path)) unlink($path);
        $adjunto->delete();
        return back()->with('success', 'Archivo eliminado.');
    }
}
