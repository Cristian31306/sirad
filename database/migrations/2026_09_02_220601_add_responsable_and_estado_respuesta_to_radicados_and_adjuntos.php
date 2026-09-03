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
        Schema::table('radicado_adjuntos', function (Blueprint $table) {
            $table->foreignId('responsable_id')->nullable()->after('radicado_id')->constrained('responsables')->nullOnDelete();
        });

        Schema::table('radicados', function (Blueprint $table) {
            $table->string('estado_respuesta')->default('sin_respuesta')->after('estado');
            $table->foreignId('respuesta_marcada_por')->nullable()->after('estado_respuesta')->constrained('responsables')->nullOnDelete();
            $table->timestamp('fecha_respuesta_marcada')->nullable()->after('respuesta_marcada_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->dropForeign(['respuesta_marcada_por']);
            $table->dropColumn(['estado_respuesta', 'respuesta_marcada_por', 'fecha_respuesta_marcada']);
        });

        Schema::table('radicado_adjuntos', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn(['responsable_id']);
        });
    }
};
