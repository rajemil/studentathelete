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
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('semester');
            $table->decimal('gpa', 4, 2);
            $table->integer('credits_earned');
            $table->string('status')->default('good_standing'); // good_standing, warning, probation, ineligible
            $table->timestamps();

            $table->index(['user_id', 'semester']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('course_name');
            $table->date('date');
            $table->string('status')->default('present'); // present, absent, excused, tardy
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
        });

        Schema::create('eligibility_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->date('review_date');
            $table->string('status')->default('pending'); // eligible, ineligible, pending, probation
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'review_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eligibility_reviews');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('academic_records');
    }
};
