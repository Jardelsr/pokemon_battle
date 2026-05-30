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
        Schema::create('battles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('pokemon_one_name');
            $table->integer('pokemon_one_hp');

            $table->string('pokemon_two_name');
            $table->integer('pokemon_two_hp');

            $table->string('winner_name')->nullable();

            $table->enum('result', [
                'pokemon_one_wins',
                'pokemon_two_wins',
                'draw',
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
