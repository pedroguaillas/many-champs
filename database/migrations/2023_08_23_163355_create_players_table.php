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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id'); // Refiere al campeonato
            $table->unsignedBigInteger('club_id');
            $table->string('cedula', 10)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('photo', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('t_shirt')->nullable();
            $table->string('phone', 10)->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('club_id')->references('id')->on('clubs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
