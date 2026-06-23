<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('actividad_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $t->string('accion'); $t->string('modulo'); $t->string('descripcion');
            $t->string('referencia')->nullable(); $t->string('ip')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('actividad_log'); }
};
