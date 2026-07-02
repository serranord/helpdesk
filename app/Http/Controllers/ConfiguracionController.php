<?php
namespace App\Http\Controllers;
use App\Models\Configuracion;
use App\Models\MenuAplicacion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller {

    public function index() {
        $configs = Configuracion::all()->keyBy('clave');
        $menus   = MenuAplicacion::orderBy('orden')->get();
        return view('admin.configuracion', compact('configs','menus'));
    }

    public function update(Request $request) {
        Configuracion::set('registro_habilitado', $request->boolean('registro_habilitado') ? '1' : '0');
        Configuracion::set('solo_sso',            $request->boolean('solo_sso') ? '1' : '0');
        return back()->with('success', 'Configuración guardada correctamente.');
    }

    // Menú de aplicaciones
    public function storeMenu(Request $request) {
        $data = $request->validate([
            'nombre'       => 'required|string|max:50',
            'url'          => 'required|string|max:255',
            'icono'        => 'nullable|string|max:10',
            'orden'        => 'nullable|integer',
            'nueva_ventana'=> 'boolean',
        ]);
        MenuAplicacion::create($data);
        return back()->with('success', 'Enlace agregado.');
    }

    public function updateMenu(Request $request, MenuAplicacion $menuAplicacion) {
        $data = $request->validate([
            'nombre'       => 'required|string|max:50',
            'url'          => 'required|string|max:255',
            'icono'        => 'nullable|string|max:10',
            'orden'        => 'nullable|integer',
            'activo'       => 'boolean',
            'nueva_ventana'=> 'boolean',
        ]);
        $menuAplicacion->update($data);
        return back()->with('success', 'Enlace actualizado.');
    }

    public function destroyMenu(MenuAplicacion $menuAplicacion) {
        $menuAplicacion->delete();
        return back()->with('success', 'Enlace eliminado.');
    }
}
