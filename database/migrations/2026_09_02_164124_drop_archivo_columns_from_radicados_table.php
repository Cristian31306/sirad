<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->dropColumn([
                'archivo_entrada_path',
                'archivo_entrada_nombre',
                'archivo_salida_path',
                'archivo_salida_nombre',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->string('archivo_entrada_path')->nullable();
            $table->string('archivo_entrada_nombre')->nullable();
            $table->string('archivo_salida_path')->nullable();
            $table->string('archivo_salida_nombre')->nullable();
        });
    }
};
