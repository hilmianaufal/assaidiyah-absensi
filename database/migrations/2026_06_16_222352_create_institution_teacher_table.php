<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_teacher', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institution_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['institution_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_teacher');
    }
};
