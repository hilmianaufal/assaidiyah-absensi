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
        Schema::create('honor_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monthly_honor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('payment_date');
            $table->unsignedInteger('amount')->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honor_payments');
    }
};
