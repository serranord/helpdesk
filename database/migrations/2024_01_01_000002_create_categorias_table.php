<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categorias', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->string('descripcion')->nullable();
            $t->string('icono')->default('🖥️');
            $t->integer('sla_horas')->default(24);
            $t->boolean('activa')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('categorias'); }
};
