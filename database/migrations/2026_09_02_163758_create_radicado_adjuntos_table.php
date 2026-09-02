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
        Schema::create('radicado_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radicado_id')->constrained('radicados')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida'])->default('entrada');
            $table->string('path');
            $table->string('nombre_original');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radicado_adjuntos');
    }
};
