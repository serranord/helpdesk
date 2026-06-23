<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\ActividadLog;

class AuthController extends Controller {

    // ── LOGIN ──────────────────────────────────────────────────────────────
    public function showLogin() {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'correo'   => 'required|email',
            'password' => 'required',
        ], [
            'correo.required'   => 'El correo es requerido.',
            'correo.email'      => 'El correo no es válido.',
            'password.required' => 'La contraseña es requerida.',
        ]);

        if (Auth::attempt(['correo' => $request->correo, 'password' => $request->password, 'estado' => 'activo'], $request->boolean('remember'))) {
            $request->session()->regenerate();
            ActividadLog::registrar('login', 'sesion', 'Inició sesión en el sistema');
            return redirect()->intended(route('dashboard'));
        }

        return back()->withInput($request->only('correo'))
            ->withErrors(['correo' => 'Credenciales incorrectas o cuenta inactiva.']);
    }

    public function logout(Request $request) {
        ActividadLog::registrar('logout', 'sesion', 'Cerró sesión');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ── REGISTRO ───────────────────────────────────────────────────────────
    public function showRegister() {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function register(Request $request) {
        $dominioPermitido = '@amcham.org.do';

        $request->validate([
            'nombre'    => 'required|string|max:100',
            'apellido'  => 'required|string|max:100',
            'correo'    => 'required|email|unique:usuarios,correo',
            'password'  => 'required|min:6|confirmed',
            'departamento' => 'nullable|string|max:100',
        ], [
            'nombre.required'    => 'El nombre es requerido.',
            'apellido.required'  => 'El apellido es requerido.',
            'correo.required'    => 'El correo es requerido.',
            'correo.email'       => 'El correo no tiene un formato válido.',
            'correo.unique'      => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es requerida.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Validar dominio institucional
        if (!str_ends_with(strtolower($request->correo), $dominioPermitido)) {
            return back()->withInput($request->only('nombre','apellido','correo','departamento'))
                ->withErrors(['correo' => 'Solo se permiten correos institucionales de AmCham. Tu correo debe terminar en @amcham.org.do']);
        }

        $usuario = Usuario::create([
            'nombre'       => trim($request->nombre . ' ' . $request->apellido),
            'correo'       => strtolower($request->correo),
            'password'     => Hash::make($request->password),
            'rol'          => 'solicitante',
            'estado'       => 'activo',
            'departamento' => $request->departamento,
        ]);

        Auth::login($usuario);
        ActividadLog::registrar('registro', 'sesion', 'Se registró en el sistema');

        return redirect()->route('dashboard')->with('success', '¡Bienvenido/a ' . $request->nombre . '! Tu cuenta ha sido creada.');
    }
}
