<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // Previous uniqueness was global per sport; with multitenancy it must include organization_id.
            $table->dropUnique('teams_sport_id_name_unique');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unique(['organization_id', 'sport_id', 'name']);
            $table->index(['organization_id', 'sport_id']);
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'sport_id', 'name']);
            $table->dropIndex(['organization_id', 'sport_id']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unique(['sport_id', 'name']);
        });
    }
};
