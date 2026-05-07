<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('pending'); // pending, approved, rejected, withdrawn
            $table->boolean('qualification_passed')->default(false);
            $table->json('qualification_detail')->nullable();
            $table->text('student_message')->nullable();
            $table->timestamps();

            $table->unique(['sport_id', 'user_id']);
            $table->index(['sport_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_applications');
    }
};
