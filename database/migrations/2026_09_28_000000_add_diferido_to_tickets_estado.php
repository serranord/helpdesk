<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('estado', ['nuevo', 'abierto', 'asignado', 'en_proceso', 'pendiente', 'resuelto', 'cerrado', 'diferido'])->default('nuevo')->change();
        });
    }

    public function down(): void
    {
        if (DB::table('tickets')->where('estado', 'diferido')->exists()) {
            throw new RuntimeException('Reclasifica los tickets diferidos antes de revertir esta migración.');
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('estado', ['nuevo', 'abierto', 'asignado', 'en_proceso', 'pendiente', 'resuelto', 'cerrado'])->default('nuevo')->change();
        });
    }
};
