<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();

            $table->string('category')->default('overall'); // speed, stamina, overall, etc
            $table->decimal('score', 8, 2);
            $table->date('scored_on')->nullable();
            $table->jsonb('breakdown')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'sport_id', 'category']);
            $table->index(['team_id', 'scored_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_scores');
    }
};
