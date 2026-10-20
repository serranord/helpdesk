<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsuariosEliminadosTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('x', 32)),
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        foreach (glob(database_path('migrations/2024_*.php')) as $migration) {
            (require $migration)->up();
        }
        (require database_path('migrations/2026_07_11_000000_add_trabajando_hoy_to_tickets.php'))->up();
    }

    private function usuario(string $correo, string $rol = 'solicitante'): Usuario
    {
        return Usuario::create([
            'nombre' => $correo, 'correo' => $correo,
            'password' => 'test-password', 'rol' => $rol, 'estado' => 'activo',
        ]);
    }

    public function test_admin_can_view_deleted_accounts_separately_from_current_accounts(): void
    {
        $admin = $this->usuario('admin@example.com', 'administrador');
        $inactive = $this->usuario('inactive@example.com');
        $inactive->update(['estado' => 'inactivo']);
        $deleted = $this->usuario('deleted@example.com');
        $deleted->delete();

        $this->actingAs($admin)->get(route('usuarios.index'))
            ->assertOk()->assertSee($inactive->correo)->assertDontSee($deleted->correo);

        $this->get(route('usuarios.index', ['eliminados' => 1]))
            ->assertOk()
            ->assertSee($deleted->correo)
            ->assertSee($deleted->deleted_at->format('d/m/Y H:i'))
            ->assertDontSee($inactive->correo)
            ->assertDontSee('>Editar</a>', false)
            ->assertDontSee('>Eliminar</button>', false)
            ->assertViewHas('usuarios', fn ($usuarios) => $usuarios->total() === 1);

        $this->assertSoftDeleted('usuarios', ['id' => $deleted->id]);
    }

    public function test_deleted_filter_is_preserved_when_paginating_and_empty_state_is_clear(): void
    {
        $admin = $this->usuario('admin@example.com', 'administrador');
        $this->actingAs($admin)->get(route('usuarios.index', ['eliminados' => 1]))
            ->assertOk()->assertSee('No hay cuentas eliminadas.');

        for ($i = 0; $i < 21; $i++) {
            $this->usuario(sprintf('deleted%02d@example.com', $i))->delete();
        }
        $this->get(route('usuarios.index', ['eliminados' => 1]))
            ->assertOk()
            ->assertViewHas('usuarios', fn ($usuarios) =>
                $usuarios->total() === 21
                && $usuarios->count() === 20
                && str_contains($usuarios->nextPageUrl(), 'eliminados=1'));
        $this->get(route('usuarios.index', ['eliminados' => 1, 'page' => 2]))
            ->assertOk()->assertSee('deleted20@example.com')
            ->assertViewHas('usuarios', fn ($usuarios) => $usuarios->count() === 1);
    }

    public function test_deleted_accounts_remain_restricted_to_administrators(): void
    {
        foreach (['solicitante', 'tecnico'] as $rol) {
            $this->actingAs($this->usuario($rol.'@example.com', $rol))
                ->get(route('usuarios.index', ['eliminados' => 1]))
                ->assertForbidden();
        }
    }
}
