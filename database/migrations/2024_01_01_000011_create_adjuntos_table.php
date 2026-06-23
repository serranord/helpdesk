<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('adjuntos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->foreignId('usuario_id')->constrained('usuarios');
            $t->string('nombre_original');
            $t->string('nombre_guardado');
            $t->string('mime_type');
            $t->unsignedBigInteger('tamano'); // bytes
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('adjuntos'); }
};
