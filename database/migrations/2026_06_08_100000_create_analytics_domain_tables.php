<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athlete_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();

            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->decimal('avg_performance_score', 8, 2)->nullable();
            $table->jsonb('performance_metrics')->nullable();

            $table->unsignedSmallInteger('sessions_attended')->default(0);
            $table->unsignedSmallInteger('sessions_missed')->default(0);
            $table->decimal('attendance_rate', 5, 2)->nullable();

            $table->decimal('fatigue_score', 5, 2)->nullable();
            $table->string('injury_risk', 16)->nullable();
            $table->text('health_notes')->nullable();

            $table->timestampTz('computed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'user_id', 'sport_id']);
            $table->index(['user_id', 'period_end']);
        });

        Schema::create('competition_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('competition_name');
            $table->date('competed_on');
            $table->unsignedSmallInteger('placement')->nullable();
            $table->boolean('is_mvp')->default(false);
            $table->jsonb('stats')->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'user_id', 'competed_on']);
            $table->index(['sport_id', 'competed_on']);
            $table->index(['event_id']);
        });

        Schema::create('coach_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('athlete_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('score', 5, 2);
            $table->text('comments')->nullable();
            $table->timestampTz('evaluated_at');
            $table->timestamps();

            $table->index(['organization_id', 'coach_id', 'evaluated_at']);
            $table->index(['athlete_user_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_evaluations');
        Schema::dropIfExists('competition_records');
        Schema::dropIfExists('athlete_statistics');
    }
};
