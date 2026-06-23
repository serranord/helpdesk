<?php
namespace App\Http\Controllers;
use App\Models\Categoria; use App\Models\ActividadLog;
use Illuminate\Http\Request;

class CategoriaController extends Controller {
    public function index() {
        $categorias = Categoria::withCount('tickets')->orderBy('nombre')->get();
        return view('categorias.index', compact('categorias'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'nombre'     => 'required|string|max:100',
            'descripcion'=> 'nullable|string|max:255',
            'icono'      => 'nullable|string|max:10',
            'sla_horas'  => 'required|integer|min:1',
        ]);
        Categoria::create($data);
        ActividadLog::registrar('creó', 'categorias', "Creó categoría {$data['nombre']}");
        return back()->with('success', 'Categoría creada.');
    }
    public function update(Request $request, Categoria $categoria) {
        $data = $request->validate([
            'nombre'     => 'required|string|max:100',
            'descripcion'=> 'nullable|string|max:255',
            'icono'      => 'nullable|string|max:10',
            'sla_horas'  => 'required|integer|min:1',
            'activa'     => 'boolean',
        ]);
        $categoria->update($data);
        return back()->with('success', 'Categoría actualizada.');
    }
}
