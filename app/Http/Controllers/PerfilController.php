<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ActividadLog;

class PerfilController extends Controller {

    public function show() {
        return view('perfil.show', ['user' => auth()->user()]);
    }

    public function update(Request $request) {
        $user = auth()->user();
        $data = $request->validate([
            'nombre'       => 'required|string|max:150',
            'telefono'     => 'nullable|string|max:20',
            'departamento' => 'nullable|string|max:100',
        ], [
            'nombre.required' => 'El nombre es requerido.',
        ]);

        $user->update($data);
        ActividadLog::registrar('actualizó', 'perfil', 'Actualizó su perfil');
        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function cambiarPassword(Request $request) {
        $user = auth()->user();
        $request->validate([
            'password_actual' => 'required',
            'password'        => 'required|min:6|confirmed',
        ], [
            'password_actual.required' => 'La contraseña actual es requerida.',
            'password.required'        => 'La nueva contraseña es requerida.',
            'password.min'             => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed'       => 'Las contraseñas no coinciden.',
        ]);

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        ActividadLog::registrar('actualizó', 'perfil', 'Cambió su contraseña');
        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
