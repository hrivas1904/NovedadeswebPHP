<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas_usuario', function (Blueprint $table) {
            $table->tinyInteger('push_enviado')->default(0)->after('leida');
        });
    }

    public function down(): void
    {
        Schema::table('alertas_usuario', function (Blueprint $table) {
            $table->dropColumn('push_enviado');
        });
    }
};
