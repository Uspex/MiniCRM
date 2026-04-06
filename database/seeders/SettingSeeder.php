<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Смены — переносим из config/task.php
        if (!Setting::where('type', Setting::TYPE_SHIFTS)->exists()) {
            Setting::set(Setting::TYPE_SHIFTS, [
                ['shift' => 1, 'name' => 'Смена 1', 'start' => '07:00', 'end' => '15:30'],
                ['shift' => 2, 'name' => 'Смена 2', 'start' => '16:00', 'end' => '00:30'],
            ]);
        }

        // Подразделения — пустой список
        if (!Setting::where('type', Setting::TYPE_DEPARTMENTS)->exists()) {
            Setting::set(Setting::TYPE_DEPARTMENTS, []);
        }
    }
}
