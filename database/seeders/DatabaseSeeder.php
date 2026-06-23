<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Categoria;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios base
        Usuario::create([
            'nombre'   => 'Administrador TI',
            'correo'   => 'admin@helpdesk.local',
            'password' => Hash::make('admin123'),
            'rol'      => 'administrador',
            'estado'   => 'activo',
        ]);
        Usuario::create([
            'nombre'   => 'Técnico 1',
            'correo'   => 'tecnico@helpdesk.local',
            'password' => Hash::make('tecnico123'),
            'rol'      => 'tecnico',
            'estado'   => 'activo',
        ]);
        Usuario::create([
            'nombre'      => 'Usuario Demo',
            'correo'      => 'usuario@helpdesk.local',
            'password'    => Hash::make('usuario123'),
            'rol'         => 'solicitante',
            'estado'      => 'activo',
            'departamento'=> 'Contabilidad',
        ]);

        // Categorías predefinidas para TI
        $cats = [
            ['nombre'=>'Servidores e Infraestructura', 'icono'=>'🖥️',  'sla_horas'=>4,  'descripcion'=>'Problemas con servidores, hosting, backups'],
            ['nombre'=>'Redes y Conectividad',         'icono'=>'🌐',  'sla_horas'=>4,  'descripcion'=>'Internet, VPN, switches, Wi-Fi'],
            ['nombre'=>'Desarrollo de Software',       'icono'=>'💻',  'sla_horas'=>48, 'descripcion'=>'Bugs, nuevas funcionalidades, cambios en sistemas'],
            ['nombre'=>'Soporte de Equipos',           'icono'=>'🖨️',  'sla_horas'=>8,  'descripcion'=>'Computadoras, impresoras, periféricos'],
            ['nombre'=>'Accesos y Usuarios',           'icono'=>'🔐',  'sla_horas'=>2,  'descripcion'=>'Contraseñas, permisos, cuentas de usuario'],
            ['nombre'=>'Interno TI',                   'icono'=>'⚙️',  'sla_horas'=>72, 'descripcion'=>'Tareas internas del equipo de TI'],
        ];
        foreach ($cats as $c) {
            Categoria::create($c);
        }
    }
}
