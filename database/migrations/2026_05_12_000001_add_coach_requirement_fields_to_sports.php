<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->boolean('require_report_card')->default(false)->after('qual_allowed_genders');
            $table->boolean('require_medical_form')->default(false)->after('require_report_card');
            $table->boolean('require_bp')->default(false)->after('require_medical_form');
            $table->boolean('require_heart_rate')->default(false)->after('require_bp');
            $table->boolean('require_allergies')->default(false)->after('require_heart_rate');
        });
    }

    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn([
                'require_report_card',
                'require_medical_form',
                'require_bp',
                'require_heart_rate',
                'require_allergies',
            ]);
        });
    }
};
?>
