<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskWorkDaySeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        Task::whereNull('work_day')->chunkById(500, function ($tasks) use (&$count) {
            foreach ($tasks as $task) {
                $shiftData = Task::resolveShiftData($task->created_at);

                $task->update([
                    'work_day'    => $shiftData['work_day'],
                    'shift'       => $task->shift ?? $shiftData['shift'],
                    'work_start'  => $task->work_start ?? $shiftData['work_start'],
                    'work_finish' => $task->work_finish ?? $shiftData['work_finish'],
                ]);

                $count++;
            }
        });

        $this->command->info("Updated {$count} tasks.");
    }
}
