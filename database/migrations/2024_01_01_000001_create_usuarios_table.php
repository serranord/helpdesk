<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->string('correo')->unique();
            $t->string('password');
            $t->enum('rol', ['administrador', 'tecnico', 'solicitante'])->default('solicitante');
            $t->enum('estado', ['activo', 'inactivo'])->default('activo');
            $t->string('telefono')->nullable();
            $t->string('departamento')->nullable();
            $t->rememberToken();
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuarios');
    }
};