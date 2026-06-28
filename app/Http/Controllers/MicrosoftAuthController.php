<?php
namespace App\Http\Controllers;

use App\Services\MicrosoftAuthService;
use App\Models\Usuario;
use App\Models\ActividadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MicrosoftAuthController extends Controller
{
    public function redirect()
    {
        $service = new MicrosoftAuthService();
        return redirect($service->getAuthUrl());
    }

    public function callback(Request $request)
    {
        // Verificar que no hubo error
        if ($request->has('error')) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'Error al autenticar con Microsoft: ' . $request->error_description]);
        }

        // Verificar código
        if (!$request->has('code')) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'No se recibió el código de autorización de Microsoft.']);
        }

        $service = new MicrosoftAuthService();

        // Obtener token
        $accessToken = $service->getAccessToken($request->code);
        if (!$accessToken) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'No se pudo obtener el token de Microsoft. Intenta de nuevo.']);
        }

        // Obtener datos del usuario
        $userData = $service->getUserData($accessToken);
        if (!$userData) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'No se pudieron obtener los datos de tu cuenta Microsoft.']);
        }

        // Verificar que el correo sea del dominio permitido
        $dominio = '@amcham.org.do';
        if (!str_ends_with(strtolower($userData['correo']), $dominio)) {
            return redirect()->route('login')
                ->withErrors(['correo' => "Solo se permiten cuentas institucionales de AmCham (@amcham.org.do)."]); 
        }

        // Buscar o crear usuario
        $usuario = Usuario::withTrashed()
            ->where('microsoft_id', $userData['microsoft_id'])
            ->orWhere('correo', strtolower($userData['correo']))
            ->first();

        if ($usuario) {
            // Restaurar si estaba eliminado
            if ($usuario->trashed()) $usuario->restore();

            // Actualizar datos desde Microsoft
            $usuario->update([
                'microsoft_id'    => $userData['microsoft_id'],
                'nombre'          => $userData['nombre'],
                'departamento'    => $userData['departamento'] ?? $usuario->departamento,
                'cargo'           => $userData['cargo'] ?? $usuario->cargo,
                'login_microsoft' => true,
                'estado'          => 'activo',
            ]);
        } else {
            // Crear usuario nuevo
            $usuario = Usuario::create([
                'microsoft_id'    => $userData['microsoft_id'],
                'nombre'          => $userData['nombre'],
                'correo'          => strtolower($userData['correo']),
                'password'        => Hash::make(Str::random(32)),
                'rol'             => 'solicitante',
                'estado'          => 'activo',
                'departamento'    => $userData['departamento'] ?? null,
                'cargo'           => $userData['cargo'] ?? null,
                'login_microsoft' => true,
            ]);
        }

        // Verificar que esté activo
        if ($usuario->estado !== 'activo') {
            return redirect()->route('login')
                ->withErrors(['correo' => 'Tu cuenta está inactiva. Contacta al administrador.']);
        }

        // Iniciar sesión
        Auth::login($usuario, true);
        ActividadLog::registrar('login', 'sesion', 'Inició sesión con Microsoft 365');

        return redirect()->intended(route('dashboard'));
    }
}
