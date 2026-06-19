<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dhuha_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('institution_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('report_date');

            $table->string('status')->default('done');
            // done / not_done

            $table->integer('teacher_count')->default(0);

            $table->string('imam_name')->nullable();

            $table->text('note')->nullable();

            $table->text('whatsapp_message')->nullable();

            $table->timestamps();

            $table->unique([
                'teacher_id',
                'institution_id',
                'report_date',
            ], 'dhuha_report_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dhuha_reports');
    }
};
