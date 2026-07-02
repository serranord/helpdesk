<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('configuracion', function (Blueprint $t) {
            $t->id();
            $t->string('clave')->unique();
            $t->text('valor')->nullable();
            $t->string('descripcion')->nullable();
            $t->timestamps();
        });

        Schema::create('menu_aplicaciones', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->string('url');
            $t->string('icono')->default('🔗');
            $t->integer('orden')->default(0);
            $t->boolean('activo')->default(true);
            $t->boolean('nueva_ventana')->default(true);
            $t->timestamps();
        });

        // Configuraciones por defecto
        DB::table('configuracion')->insert([
            ['clave' => 'registro_habilitado', 'valor' => '1', 'descripcion' => 'Permitir registro manual de usuarios', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'solo_sso', 'valor' => '0', 'descripcion' => 'Solo permitir login con Microsoft 365', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Menú por defecto
        DB::table('menu_aplicaciones')->insert([
            ['nombre' => 'HelpDesk', 'url' => '/', 'icono' => '🎫', 'orden' => 1, 'activo' => true, 'nueva_ventana' => false, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Activos TI', 'url' => 'http://trackdr.amcham.org.do', 'icono' => '🖥️', 'orden' => 2, 'activo' => false, 'nueva_ventana' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('menu_aplicaciones');
        Schema::dropIfExists('configuracion');
    }
};
