<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plantillas', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->string('titulo');
            $t->text('descripcion');
            $t->foreignId('categoria_id')->constrained('categorias');
            $t->enum('prioridad', ['baja','media','alta','critica'])->default('media');
            $t->foreignId('creado_por')->constrained('usuarios');
            $t->boolean('activa')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('plantillas'); }
};
