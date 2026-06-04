<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harden foreign keys and nullability across the schema.
     *
     * 1. Make organization_id NOT NULL on users, sports, teams.
     * 2. Replace cascadeOnUpdate-only FK with cascadeOnDelete on org FKs.
     * 3. Add organization_id to academic_records, attendance_records, eligibility_reviews.
     * 4. Change eligibility_reviews.reviewer_id from cascadeOnDelete to nullOnDelete.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. users.organization_id → NOT NULL + cascadeOnDelete
        // ---------------------------------------------------------------
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });

        // ---------------------------------------------------------------
        // 2. sports.organization_id → NOT NULL + cascadeOnDelete
        // ---------------------------------------------------------------
        Schema::table('sports', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::table('sports', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });

        // ---------------------------------------------------------------
        // 3. teams.organization_id → NOT NULL + cascadeOnDelete
        // ---------------------------------------------------------------
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });

        // ---------------------------------------------------------------
        // 4. eligibility_reviews.reviewer_id → nullOnDelete (preserve audit data)
        // ---------------------------------------------------------------
        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->dropForeign(['reviewer_id']);
        });
        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewer_id')->nullable()->change();
            $table->foreign('reviewer_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // ---------------------------------------------------------------
        // 5. Add organization_id to academic_records
        // ---------------------------------------------------------------
        Schema::table('academic_records', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
        });

        // Backfill from users table
        DB::statement('
            UPDATE academic_records
            SET organization_id = (
                SELECT users.organization_id
                FROM users
                WHERE users.id = academic_records.user_id
            )
        ');

        Schema::table('academic_records', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->index(['organization_id', 'user_id'], 'acad_records_org_user_idx');
        });

        // ---------------------------------------------------------------
        // 6. Add organization_id to attendance_records
        // ---------------------------------------------------------------
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
        });

        DB::statement('
            UPDATE attendance_records
            SET organization_id = (
                SELECT users.organization_id
                FROM users
                WHERE users.id = attendance_records.user_id
            )
        ');

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->index(['organization_id', 'user_id', 'date'], 'attend_records_org_user_date_idx');
        });

        // ---------------------------------------------------------------
        // 7. Add organization_id to eligibility_reviews
        // ---------------------------------------------------------------
        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
        });

        DB::statement('
            UPDATE eligibility_reviews
            SET organization_id = (
                SELECT users.organization_id
                FROM users
                WHERE users.id = eligibility_reviews.user_id
            )
        ');

        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->index(['organization_id', 'user_id'], 'elig_reviews_org_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse 7: Drop organization_id from eligibility_reviews
        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->dropIndex('elig_reviews_org_user_idx');
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        // Reverse 6: Drop organization_id from attendance_records
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex('attend_records_org_user_date_idx');
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        // Reverse 5: Drop organization_id from academic_records
        Schema::table('academic_records', function (Blueprint $table) {
            $table->dropIndex('acad_records_org_user_idx');
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        // Reverse 4: eligibility_reviews.reviewer_id back to cascadeOnDelete
        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->dropForeign(['reviewer_id']);
        });
        Schema::table('eligibility_reviews', function (Blueprint $table) {
            $table->foreign('reviewer_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        // Reverse 3: teams.organization_id back to nullable + cascadeOnUpdate only
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnUpdate();
        });

        // Reverse 2: sports.organization_id back to nullable + cascadeOnUpdate only
        Schema::table('sports', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::table('sports', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnUpdate();
        });

        // Reverse 1: users.organization_id back to nullable + cascadeOnUpdate only
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnUpdate();
        });
    }
};
