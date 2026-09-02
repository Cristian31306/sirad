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
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->string('tipo_dias')->default('habiles')->after('dias_habiles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->dropColumn('tipo_dias');
        });
    }
};
