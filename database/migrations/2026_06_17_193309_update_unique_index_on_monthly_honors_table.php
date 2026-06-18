<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE monthly_honors
            ADD UNIQUE monthly_honors_teacher_institution_month_year_unique
            (teacher_id, institution_id, month, year)
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE monthly_honors
            DROP INDEX monthly_honors_teacher_institution_month_year_unique
        ");
    }
};
