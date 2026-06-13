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
        Schema::create('picket_report_students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('picket_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('class_name');
            $table->string('student_name');
            $table->string('status')->default('alpa');
            $table->string('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picket_report_students');
    }
};
