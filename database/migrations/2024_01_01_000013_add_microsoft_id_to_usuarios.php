<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('usuarios', function (Blueprint $t) {
            $t->string('microsoft_id')->nullable()->unique()->after('id');
            $t->string('cargo')->nullable()->after('departamento');
            $t->boolean('login_microsoft')->default(false)->after('cargo');
        });
    }
    public function down(): void {
        Schema::table('usuarios', function (Blueprint $t) {
            $t->dropColumn(['microsoft_id','cargo','login_microsoft']);
        });
    }
};
