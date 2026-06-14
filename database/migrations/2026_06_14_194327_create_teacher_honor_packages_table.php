<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_honor_packages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('weekly_hours')->default(0);

            $table->decimal('package_rate', 12, 0)->default(0);

            $table->decimal('monthly_honor', 12, 0)->default(0);

            $table->decimal('deduction_per_hour', 12, 0)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_honor_packages');
    }
};
