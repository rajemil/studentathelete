<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // student
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // coach/admin

            $table->string('title');
            $table->text('recommendation');
            $table->string('status')->default('active'); // active, completed, archived
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'sport_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_recommendations');
    }
};

