<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teaching_schedule_id')->nullable()->constrained()->nullOnDelete();

            $table->date('teaching_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('hours_count')->default(1);
            $table->integer('hourly_rate')->default(25000);
            $table->integer('teaching_honor')->default(0);

            $table->string('class_name')->nullable();
            $table->string('status')->default('present');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_attendances');
    }
};
