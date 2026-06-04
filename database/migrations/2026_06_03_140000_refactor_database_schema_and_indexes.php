<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - Drop the static `age` column (replaced by Profile::getAgeAttribute accessor).
     * - Add missing composite indexes for common query patterns.
     * - Drop redundant single-column index on insights.computed_at.
     */
    public function up(): void
    {
        // 1. Remove age from profiles table if it exists
        if (Schema::hasColumn('profiles', 'age')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('age');
            });
        }

        // 2. Add missing indexes on performance_scores
        Schema::table('performance_scores', function (Blueprint $table) {
            $table->index(['sport_id', 'scored_on'], 'perf_scores_sport_scored_idx');
            $table->index(['user_id', 'scored_on'], 'perf_scores_user_scored_idx');
        });

        // 3. Add missing index on injury_records
        Schema::table('injury_records', function (Blueprint $table) {
            $table->index(['athlete_user_id', 'created_at'], 'injury_records_athlete_created_idx');
        });

        // 4. Add missing index on player_stats
        Schema::table('player_stats', function (Blueprint $table) {
            $table->index(['user_id', 'recorded_on'], 'player_stats_user_recorded_idx');
        });

        // 5. Drop redundant single-column index on insights.computed_at
        //    (covered by composite indexes: user_id+computed_at, team_id+computed_at, etc.)
        Schema::table('insights', function (Blueprint $table) {
            $table->dropIndex('insights_computed_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the redundant insights index
        Schema::table('insights', function (Blueprint $table) {
            $table->index(['computed_at']);
        });

        // Drop player_stats index
        Schema::table('player_stats', function (Blueprint $table) {
            $table->dropIndex('player_stats_user_recorded_idx');
        });

        // Drop injury_records index
        Schema::table('injury_records', function (Blueprint $table) {
            $table->dropIndex('injury_records_athlete_created_idx');
        });

        // Drop performance_scores indexes
        Schema::table('performance_scores', function (Blueprint $table) {
            $table->dropIndex('perf_scores_user_scored_idx');
            $table->dropIndex('perf_scores_sport_scored_idx');
        });

        // Re-add the age column (will be NULL for all existing rows)
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('age')->nullable()->after('user_id');
        });
    }
};
