<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dhuha_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('dhuha_reports', 'present_teacher_ids')) {
                $table->json('present_teacher_ids')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dhuha_reports', function (Blueprint $table) {
            if (Schema::hasColumn('dhuha_reports', 'present_teacher_ids')) {
                $table->dropColumn('present_teacher_ids');
            }
        });
    }
};
