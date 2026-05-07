<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->unsignedSmallInteger('qual_min_age')->nullable()->after('description');
            $table->unsignedSmallInteger('qual_max_age')->nullable()->after('qual_min_age');
            $table->unsignedSmallInteger('qual_min_height_cm')->nullable()->after('qual_max_age');
            $table->json('qual_allowed_genders')->nullable()->after('qual_min_height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn(['qual_min_age', 'qual_max_age', 'qual_min_height_cm', 'qual_allowed_genders']);
        });
    }
};
