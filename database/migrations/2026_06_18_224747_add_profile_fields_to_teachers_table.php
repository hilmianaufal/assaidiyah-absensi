<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'address')) {
                $table->string('address')->nullable();
            }

            if (! Schema::hasColumn('teachers', 'bio')) {
                $table->text('bio')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('teachers', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};
