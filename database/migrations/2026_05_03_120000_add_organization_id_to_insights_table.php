<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('hash_key')->constrained()->nullOnDelete();
            $table->index(['organization_id', 'computed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('insights', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'computed_at']);
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
