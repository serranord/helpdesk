<?php
namespace App\Http\Controllers;
use App\Models\Plantilla; use App\Models\Categoria;
use Illuminate\Http\Request;

class PlantillaController extends Controller {

    public function index() {
        $plantillas = Plantilla::with(['categoria','creadoPor'])->orderBy('nombre')->get();
        $categorias = Categoria::where('activa',true)->get();
        return view('plantillas.index', compact('plantillas','categorias'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nombre'       => 'required|string|max:100',
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'prioridad'    => 'required|in:baja,media,alta,critica',
        ]);
        $data['creado_por'] = auth()->id();
        Plantilla::create($data);
        return back()->with('success', 'Plantilla creada correctamente.');
    }

    public function update(Request $request, Plantilla $plantilla) {
        $data = $request->validate([
            'nombre'       => 'required|string|max:100',
            'titulo'       => 'required|string|max:255',
            'descripcion'  => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'prioridad'    => 'required|in:baja,media,alta,critica',
            'activa'       => 'boolean',
        ]);
        $plantilla->update($data);
        return back()->with('success', 'Plantilla actualizada.');
    }

    public function destroy(Plantilla $plantilla) {
        $plantilla->delete();
        return back()->with('success', 'Plantilla eliminada.');
    }

    // API: devuelve datos de plantilla para rellenar el form
    public function datos(Plantilla $plantilla) {
        return response()->json($plantilla->load('categoria'));
    }
}
