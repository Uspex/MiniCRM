<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Смены — из config/task.php
        if (!Setting::where('type', Setting::TYPE_SHIFTS)->exists()) {
            Setting::set(Setting::TYPE_SHIFTS, config('task.shifts', []));
        }

        // Подразделения — пустой список
        if (!Setting::where('type', Setting::TYPE_DEPARTMENTS)->exists()) {
            Setting::set(Setting::TYPE_DEPARTMENTS, []);
        }
    }
}
