<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_honors', function (Blueprint $table) {
            $table->unsignedInteger('total_additional_honor')
                ->default(0)
                ->after('total_transport');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_honors', function (Blueprint $table) {
            $table->dropColumn('total_additional_honor');
        });
    }
};
