<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('radicado_responsable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radicado_id')->constrained('radicados')->onDelete('cascade');
            $table->foreignId('responsable_id')->constrained('responsables')->onDelete('cascade');
            $table->timestamps();
        });

        // Migrar datos existentes
        $radicados = DB::table('radicados')->whereNotNull('responsable_id')->get();
        foreach ($radicados as $radicado) {
            DB::table('radicado_responsable')->insert([
                'radicado_id' => $radicado->id,
                'responsable_id' => $radicado->responsable_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Eliminar la llave foránea y la columna
        Schema::table('radicados', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn('responsable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->nullOnDelete();
        });

        // Revertir datos (solo tomará el último si hay múltiples)
        $relations = DB::table('radicado_responsable')->get();
        foreach ($relations as $rel) {
            DB::table('radicados')->where('id', $rel->radicado_id)->update(['responsable_id' => $rel->responsable_id]);
        }

        Schema::dropIfExists('radicado_responsable');
    }
};
