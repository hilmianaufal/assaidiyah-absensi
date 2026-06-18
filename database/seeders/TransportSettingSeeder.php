<?php

namespace Database\Seeders;

use App\Models\TransportSetting;
use Illuminate\Database\Seeder;

class TransportSettingSeeder extends Seeder
{
    public function run(): void
    {
        TransportSetting::updateOrCreate(
            ['id' => 1],
            [
                'check_in_start' => '06:45:00',
                'check_in_end' => '07:15:00',
                'check_out_start' => '12:45:00',
                'check_out_end' => '13:15:00',
                'amount' => 10000,
                'is_active' => true,
            ]
        );
    }
}
