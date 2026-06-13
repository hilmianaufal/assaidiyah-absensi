<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_attendances', function (Blueprint $table) {
            $table->foreignId('recorded_by_teacher_id')
                ->nullable()
                ->after('teaching_schedule_id')
                ->constrained('teachers')
                ->nullOnDelete();

            $table->string('source')->default('admin')->after('recorded_by_teacher_id');

            $table->string('attendance_status')->default('present')->after('source');

            $table->timestamp('recorded_at')->nullable()->after('attendance_status');
        });
    }

    public function down(): void
    {
        Schema::table('subject_attendances', function (Blueprint $table) {
            $table->dropForeign(['recorded_by_teacher_id']);

            $table->dropColumn([
                'recorded_by_teacher_id',
                'source',
                'attendance_status',
                'recorded_at',
            ]);
        });
    }
};