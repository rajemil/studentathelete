<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampTz('starts_at')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->string('event_type')->default('training'); // training, game, tryout, meeting
            $table->timestamps();

            $table->index(['team_id', 'starts_at']);
            $table->index(['sport_id', 'starts_at']);
        });

        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('participant_role')->default('student'); // student, coach
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index(['user_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_participants');
        Schema::dropIfExists('events');
    }
};

