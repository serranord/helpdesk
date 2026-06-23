<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('calificaciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->unique()->constrained('tickets')->cascadeOnDelete();
            $t->foreignId('usuario_id')->constrained('usuarios');
            $t->unsignedTinyInteger('estrellas'); // 1-5
            $t->text('comentario')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('calificaciones'); }
};
