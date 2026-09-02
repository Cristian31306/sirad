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
        Schema::table('radicado_responsable', function (Blueprint $table) {
            $table->boolean('hubo_rebote')->default(false);
            $table->timestamp('fecha_rebote')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radicado_responsable', function (Blueprint $table) {
            $table->dropColumn(['hubo_rebote', 'fecha_rebote']);
        });
    }
};
