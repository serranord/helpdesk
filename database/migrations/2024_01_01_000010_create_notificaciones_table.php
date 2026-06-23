<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notificaciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $t->string('tipo');        // ticket_asignado, comentario, estado_cambiado, etc.
            $t->string('titulo');
            $t->string('mensaje');
            $t->string('url')->nullable();
            $t->string('referencia')->nullable(); // número de ticket
            $t->timestamp('leida_en')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notificaciones'); }
};
