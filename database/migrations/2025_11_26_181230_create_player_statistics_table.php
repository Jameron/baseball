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
        Schema::create('player_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
            
            // Game statistics
            $table->unsignedInteger('games')->default(0);
            $table->unsignedInteger('at_bat')->default(0);
            $table->unsignedInteger('runs')->default(0);
            $table->unsignedInteger('hits')->default(0);
            $table->unsignedInteger('doubles')->default(0);
            $table->unsignedInteger('triples')->default(0);
            $table->unsignedInteger('home_runs')->default(0);
            $table->unsignedInteger('rbi')->default(0); // Runs Batted In
            $table->unsignedInteger('walks')->default(0);
            $table->unsignedInteger('strikeouts')->default(0);
            $table->unsignedInteger('stolen_bases')->default(0);
            $table->unsignedInteger('caught_stealing')->default(0);
            
            // Calculated statistics (decimal with appropriate precision)
            $table->decimal('batting_average', 5, 3)->nullable(); // AVG
            $table->decimal('on_base_percentage', 5, 3)->nullable(); // OBP
            $table->decimal('slugging_percentage', 5, 3)->nullable(); // SLG
            $table->decimal('on_base_plus_slugging', 5, 3)->nullable(); // OPS
            
            $table->timestamps();
            
            // Index for faster queries
            $table->index('player_id');
            $table->index('position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_statistics');
    }
};
