<?php
namespace App\Http\Controllers;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller {

    public function index() {
        $notificaciones = Notificacion::where('usuario_id', auth()->id())
            ->orderByDesc('created_at')->paginate(20);
        // Marcar todas como leídas
        Notificacion::where('usuario_id', auth()->id())->whereNull('leida_en')->update(['leida_en' => now()]);
        return view('notificaciones.index', compact('notificaciones'));
    }

    public function noLeidas() {
        $count = Notificacion::where('usuario_id', auth()->id())->whereNull('leida_en')->count();
        $items = Notificacion::where('usuario_id', auth()->id())
            ->whereNull('leida_en')->orderByDesc('created_at')->limit(8)->get();
        return response()->json(compact('count','items'));
    }

    public function marcarLeida(Notificacion $notificacion) {
        if ($notificacion->usuario_id === auth()->id())
            $notificacion->update(['leida_en' => now()]);
        return response()->json(['ok' => true]);
    }

    public function marcarTodasLeidas() {
        Notificacion::where('usuario_id', auth()->id())->whereNull('leida_en')->update(['leida_en' => now()]);
        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}
