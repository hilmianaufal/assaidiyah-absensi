<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_settings', function (Blueprint $table) {
            $table->id();

            $table->time('check_in_start')->default('06:45:00');
            $table->time('check_in_end')->default('07:15:00');

            $table->time('check_out_start')->default('12:45:00');
            $table->time('check_out_end')->default('13:15:00');

            $table->decimal('amount', 12, 0)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_settings');
    }
};
