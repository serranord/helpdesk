<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('avisos_ti', function (Blueprint $t) {
            $t->id();
            $t->string('titulo');
            $t->text('mensaje');
            $t->enum('tipo', ['info','advertencia','critico','mantenimiento','resuelto'])->default('info');
            $t->boolean('activo')->default(true);
            $t->timestamp('expira_en')->nullable();
            $t->foreignId('creado_por')->constrained('usuarios');
            $t->timestamps();
        });

        Schema::create('tickets_vinculados', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_padre_id')->constrained('tickets')->cascadeOnDelete();
            $t->foreignId('ticket_hijo_id')->constrained('tickets')->cascadeOnDelete();
            $t->timestamps();
        });

        Schema::table('tickets', function (Blueprint $t) {
            $t->timestamp('estimado_en')->nullable()->after('fecha_limite'); // tiempo estimado de atención
            $t->boolean('reabierto')->default(false)->after('nota_cierre');
        });
    }
    public function down(): void {
        Schema::table('tickets', function(Blueprint $t) {
            $t->dropColumn(['estimado_en','reabierto']);
        });
        Schema::dropIfExists('tickets_vinculados');
        Schema::dropIfExists('avisos_ti');
    }
};
