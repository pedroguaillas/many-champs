<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Posición del jugador en la cancha, relativa a su equipo:
         * pos_x 0 = su arco, 100 = arco rival; pos_y 0-100 de banda a banda.
         * null = posición automática.
         */
        Schema::table('game_items', function (Blueprint $table) {
            $table->decimal('pos_x', 5, 2)->nullable()->after('note');
            $table->decimal('pos_y', 5, 2)->nullable()->after('pos_x');
        });

        // Color de camiseta del club (#rrggbb). null = colores por defecto en cancha
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('name');
        });

        // Página pública del campeonato: apagada hasta que el organizador comparte.
        // El slug se crea al compartir por primera vez y ya no cambia (los enlaces no se rompen)
        Schema::table('teams', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->unique()->after('name');
            $table->boolean('is_public')->default(false)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'is_public']);
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('game_items', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y']);
        });
    }
};
