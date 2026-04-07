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
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->string('hash_key')->unique();

            // Scope: global/system, team, sport, or user-specific
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 64);      // performance_improved, stamina_decreasing, top_performer_week, at_risk_injury
            $table->string('severity', 16);  // info, success, warning, danger
            $table->string('title', 140);
            $table->text('message');

            $table->jsonb('payload')->nullable();
            $table->timestampTz('computed_at');

            $table->timestamps();

            $table->index(['computed_at']);
            $table->index(['user_id', 'computed_at']);
            $table->index(['team_id', 'computed_at']);
            $table->index(['sport_id', 'computed_at']);
            $table->index(['type', 'computed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
