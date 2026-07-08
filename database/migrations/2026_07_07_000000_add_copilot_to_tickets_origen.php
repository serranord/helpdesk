<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY origen ENUM('usuario','tecnico','copilot') DEFAULT 'usuario'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY origen ENUM('usuario','tecnico') DEFAULT 'usuario'");
    }
};
