<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->string('numero')->unique();
            $t->string('titulo');
            $t->text('descripcion');
            $t->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $t->enum('estado', ['nuevo', 'abierto', 'asignado', 'en_proceso', 'pendiente', 'resuelto', 'cerrado'])->default('nuevo');
            $t->enum('origen', ['usuario', 'tecnico'])->default('usuario');
            $t->foreignId('categoria_id')->constrained('categorias');
            $t->foreignId('solicitante_id')->constrained('usuarios');
            $t->foreignId('tecnico_id')->nullable()->constrained('usuarios');
            $t->foreignId('creado_por')->constrained('usuarios');
            $t->timestamp('fecha_limite')->nullable();
            $t->timestamp('fecha_resolucion')->nullable();
            $t->text('nota_cierre')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); }
};
