<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();

            $table->date('recorded_on')->nullable();
            $table->string('season')->nullable(); // e.g. 2025-2026
            $table->jsonb('metrics')->nullable(); // flexible per-sport stats
            $table->timestamps();

            $table->index(['user_id', 'sport_id']);
            $table->index(['team_id', 'recorded_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_stats');
    }
};

