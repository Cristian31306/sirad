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
        Schema::create('radicados', function (Blueprint $table) {
            $table->id();
            $table->string('numero_radicado')->unique();
            $table->date('fecha_radicacion');
            $table->string('remitente');
            $table->text('asunto');
            $table->enum('tipo_tramite', ['derecho_peticion', 'solicitud_visita']);
            $table->string('pdf_entrada_path');
            $table->date('fecha_limite');
            $table->foreignId('funcionario_id')->constrained('users');
            $table->enum('estado', ['pendiente', 'alerta', 'vencido', 'completado', 'anulado'])->default('pendiente');
            $table->string('pdf_respuesta_path')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->string('motivo_anulacion')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radicados');
    }
};
