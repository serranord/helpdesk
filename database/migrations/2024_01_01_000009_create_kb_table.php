<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kb_articulos', function (Blueprint $t) {
            $t->id();
            $t->string('titulo');
            $t->text('contenido');
            $t->string('slug')->unique();
            $t->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $t->foreignId('autor_id')->constrained('usuarios');
            $t->enum('estado', ['borrador','publicado'])->default('borrador');
            $t->integer('vistas')->default(0);
            $t->boolean('destacado')->default(false);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('kb_articulos'); }
};
