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
Schema::table('daily_attendances', function (Blueprint $table) {
    $table->string('check_in_photo')->nullable()->after('check_in_status');
    $table->string('check_out_photo')->nullable()->after('check_out_status');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            //
        });
    }
};
