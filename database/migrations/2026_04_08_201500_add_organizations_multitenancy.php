<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = now();
        $defaultOrgId = DB::table('organizations')->insertGetId([
            'name' => 'Default Organization',
            'slug' => 'default',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->cascadeOnUpdate();
        });

        DB::table('users')->update(['organization_id' => $defaultOrgId]);

        Schema::table('sports', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);
        });

        Schema::table('sports', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->cascadeOnUpdate();
        });

        DB::table('sports')->update(['organization_id' => $defaultOrgId]);

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->cascadeOnUpdate();
        });

        DB::table('teams')->update(['organization_id' => $defaultOrgId]);

        Schema::table('sports', function (Blueprint $table) {
            $table->unique(['organization_id', 'name']);
            $table->unique(['organization_id', 'slug']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Organization multitenancy migration cannot be safely reversed.');
    }
};
