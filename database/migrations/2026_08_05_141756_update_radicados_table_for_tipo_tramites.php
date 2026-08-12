<?php

use App\Models\TipoTramite;
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
        // 1. Create default data
        $derechoPeticion = TipoTramite::create([
            'nombre' => 'Derecho de Petición',
            'dias_habiles' => 15,
        ]);

        $solicitudVisita = TipoTramite::create([
            'nombre' => 'Solicitud de Visita',
            'dias_habiles' => 30,
        ]);

        // 2. Add new column to radicados
        Schema::table('radicados', function (Blueprint $table) {
            $table->foreignId('tipo_tramite_id')->nullable()->constrained('tipo_tramites');
        });

        // 3. Migrate existing data
        DB::table('radicados')->where('tipo_tramite', 'derecho_peticion')->update(['tipo_tramite_id' => $derechoPeticion->id]);
        DB::table('radicados')->where('tipo_tramite', 'solicitud_visita')->update(['tipo_tramite_id' => $solicitudVisita->id]);

        // 4. Drop old column
        Schema::table('radicados', function (Blueprint $table) {
            $table->dropColumn('tipo_tramite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radicados', function (Blueprint $table) {
            $table->string('tipo_tramite')->nullable();
        });

        Schema::table('radicados', function (Blueprint $table) {
            $table->dropForeign(['tipo_tramite_id']);
            $table->dropColumn('tipo_tramite_id');
        });
    }
};
