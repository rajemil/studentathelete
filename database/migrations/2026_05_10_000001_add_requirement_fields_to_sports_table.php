<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->boolean('qual_require_report_card')->default(false)->after('qual_allowed_genders');
            $table->boolean('qual_require_medical_history')->default(false)->after('qual_require_report_card');
            $table->boolean('qual_require_other_forms')->default(false)->after('qual_require_medical_history');
        });
    }

    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn(['qual_require_report_card', 'qual_require_medical_history', 'qual_require_other_forms']);
        });
    }
};
?>
