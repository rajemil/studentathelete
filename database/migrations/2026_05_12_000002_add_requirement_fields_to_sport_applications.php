<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('sport_applications', function (Blueprint $table) {
            $table->string('report_card_path')->nullable()->after('student_message');
            $table->string('medical_bp')->nullable()->after('report_card_path');
            $table->string('medical_heart_rate')->nullable()->after('medical_bp');
            $table->text('medical_allergies')->nullable()->after('medical_heart_rate');
            $table->string('other_document_path')->nullable()->after('medical_allergies');
        });
    }
    public function down(): void {
        Schema::table('sport_applications', function (Blueprint $table) {
            $table->dropColumn(['report_card_path','medical_bp','medical_heart_rate','medical_allergies','other_document_path']);
        });
    }
};
?>
