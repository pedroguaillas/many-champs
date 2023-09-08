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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club1_id'); // Cambio team por club
            $table->unsignedBigInteger('club2_id'); // Cambio team por club
            $table->unsignedBigInteger('captain1_id')->nullable();
            $table->unsignedBigInteger('captain2_id')->nullable();
            $table->unsignedBigInteger('progress_id'); // El juego pertenece a una fase
            $table->string('state')->default('creado');
            $table->date('date');
            $table->time('time');
            $table->timestamps();

            $table->foreign('club1_id')->references('id')->on('clubs'); // Cambio team por club
            $table->foreign('club2_id')->references('id')->on('clubs'); // Cambio team por club
            $table->foreign('captain1_id')->references('id')->on('players');
            $table->foreign('captain2_id')->references('id')->on('players');
            $table->foreign('progress_id')->references('id')->on('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
