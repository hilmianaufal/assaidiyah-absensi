<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->time('check_in_time')->nullable()->after('attendance_date');
            $table->time('check_out_time')->nullable()->after('check_in_time');

            $table->string('check_in_status')->nullable()->after('check_out_time');
            $table->string('check_out_status')->nullable()->after('check_in_status');

            $table->time('attendance_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_time',
                'check_out_time',
                'check_in_status',
                'check_out_status',
            ]);

            $table->time('attendance_time')->nullable(false)->change();
        });
    }
};