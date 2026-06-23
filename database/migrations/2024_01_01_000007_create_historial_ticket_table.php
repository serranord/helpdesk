<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('historial_ticket', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $t->string('accion');        // 'estado', 'asignacion', 'prioridad', 'comentario'
            $t->string('campo')->nullable();
            $t->string('valor_anterior')->nullable();
            $t->string('valor_nuevo')->nullable();
            $t->string('descripcion');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('historial_ticket'); }
};
