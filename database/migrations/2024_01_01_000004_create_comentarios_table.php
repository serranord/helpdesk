<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comentarios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->foreignId('usuario_id')->constrained('usuarios');
            $t->text('contenido');
            $t->boolean('es_interno')->default(false);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('comentarios'); }
};
