<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('categorias', function (Blueprint $t) {
            $t->boolean('visible_usuario')->default(false)->after('activa');
        });
    }
    public function down(): void {
        Schema::table('categorias', function (Blueprint $t) {
            $t->dropColumn('visible_usuario');
        });
    }
};
