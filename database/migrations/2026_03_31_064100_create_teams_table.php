<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();

            // Primary coach (optional; assignments tracked separately)
            $table->foreignId('primary_coach_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['sport_id', 'name']);
            $table->index(['primary_coach_id']);
        });

        // Teams consist of ranked students
        Schema::create('team_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student user

            $table->unsignedSmallInteger('rank')->default(0);
            $table->date('joined_on')->nullable();
            $table->date('left_on')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
            $table->index(['team_id', 'rank']);
            $table->index(['user_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_memberships');
        Schema::dropIfExists('teams');
    }
};

