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
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('fatigue_score')->nullable()->after('bmi');
            $table->string('injury_risk', 16)->nullable()->after('fatigue_score'); // low, medium, high
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['fatigue_score', 'injury_risk']);
        });
    }
};
