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
        Schema::create('game_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('game_id');
            $table->string('entered_in');
            $table->unsignedBigInteger('change_player_id')->nullable();
            $table->integer('goals')->default(0);
            $table->string('santion')->nullable();
            $table->date('paid_santion')->nullable();
            $table->boolean('card_black')->default(false);
            $table->date('paid_black')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('player_id')->references('id')->on('players');
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('change_player_id')->references('id')->on('players');
            $table->unique(['player_id', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_items');
    }
};
