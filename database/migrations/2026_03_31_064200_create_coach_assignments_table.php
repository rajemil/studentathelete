<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('assignment_role')->default('coach'); // head_coach, assistant_coach, etc
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();

            $table->unique(['coach_id', 'team_id', 'assignment_role']);
            $table->index(['team_id', 'coach_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_assignments');
    }
};
