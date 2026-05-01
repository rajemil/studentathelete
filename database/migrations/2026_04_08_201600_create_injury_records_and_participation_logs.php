<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('injury_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('athlete_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('open'); // open, monitoring, cleared
            $table->date('occurred_on');
            $table->timestamps();

            $table->index(['organization_id', 'athlete_user_id']);
            $table->index(['occurred_on']);
        });

        Schema::create('participation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type', 64); // training, competition, recovery, other
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->date('logged_on');
            $table->timestamps();

            $table->index(['organization_id', 'user_id', 'logged_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participation_logs');
        Schema::dropIfExists('injury_records');
    }
};
