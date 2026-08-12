<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->string('empresa')->nullable()->after('remitente');
            $table->string('medio')->nullable()->after('asunto');
            $table->string('prioridad')->default('Media')->after('medio');
            $table->text('observaciones')->nullable()->after('estado');
            $table->time('hora_recepcion')->nullable()->after('fecha_radicacion');
            $table->foreignId('responsable_id')->nullable()->after('funcionario_id')->constrained('responsables')->nullOnDelete();

            $table->unsignedBigInteger('funcionario_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn(['empresa', 'medio', 'prioridad', 'observaciones', 'hora_recepcion', 'responsable_id']);
            $table->unsignedBigInteger('funcionario_id')->nullable(false)->change();
        });
    }
};
