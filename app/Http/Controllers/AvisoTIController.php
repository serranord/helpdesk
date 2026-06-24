<?php
namespace App\Http\Controllers;
use App\Models\AvisoTI;
use Illuminate\Http\Request;

class AvisoTIController extends Controller {

    public function store(Request $request) {
        $data = $request->validate([
            'titulo'    => 'required|string|max:150',
            'mensaje'   => 'required|string|max:1000',
            'tipo'      => 'required|in:info,advertencia,critico,mantenimiento,resuelto',
            'expira_en' => 'nullable|date|after:now',
        ]);
        $data['creado_por'] = auth()->id();
        AvisoTI::create($data);
        return back()->with('success', 'Aviso publicado correctamente.');
    }

    public function destroy(AvisoTI $avisoTI) {
        $avisoTI->update(['activo' => false]);
        return back()->with('success', 'Aviso desactivado.');
    }
}
