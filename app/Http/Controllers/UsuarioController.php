<?php
namespace App\Http\Controllers;
use App\Models\Usuario; use App\Models\ActividadLog;
use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller {
    public function index() {
        $usuarios = Usuario::orderBy('nombre')->paginate(20);
        return view('usuarios.index', compact('usuarios'));
    }
    public function create() { return view('usuarios.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'nombre'      => 'required|string|max:150',
            'correo'      => 'required|email|unique:usuarios,correo',
            'password'    => 'required|min:6|confirmed',
            'rol'         => 'required|in:administrador,tecnico,solicitante',
            'telefono'    => 'nullable|string|max:20',
            'departamento'=> 'nullable|string|max:100',
        ]);
        $data['password'] = Hash::make($data['password']);
        $u = Usuario::create($data);
        ActividadLog::registrar('creó', 'usuarios', "Creó usuario {$u->nombre}");
        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }
    public function edit(Usuario $usuario) { return view('usuarios.edit', compact('usuario')); }
    public function update(Request $request, Usuario $usuario) {
        $data = $request->validate([
            'nombre'      => 'required|string|max:150',
            'correo'      => 'required|email|unique:usuarios,correo,'.$usuario->id,
            'rol'         => 'required|in:administrador,tecnico,solicitante',
            'estado'      => 'required|in:activo,inactivo',
            'telefono'    => 'nullable|string|max:20',
            'departamento'=> 'nullable|string|max:100',
        ]);
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }
        $usuario->update($data);
        ActividadLog::registrar('editó', 'usuarios', "Editó usuario {$usuario->nombre}");
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }
    public function destroy(Usuario $usuario) {
        if ($usuario->id === auth()->id()) return back()->with('error', 'No puedes eliminarte a ti mismo.');
        $usuario->delete();
        ActividadLog::registrar('eliminó', 'usuarios', "Eliminó usuario {$usuario->nombre}");
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
