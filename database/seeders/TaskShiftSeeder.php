<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskShiftSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        Task::where(function ($q) {
            $q->whereNull('shift')
              ->orWhereNull('work_start')
              ->orWhereNull('work_finish');
        })->chunkById(500, function ($tasks) use (&$count) {
            foreach ($tasks as $task) {
                $shiftData = Task::resolveShiftData($task->created_at);

                $task->update([
                    'shift'       => $shiftData['shift'],
                    'work_start'  => $shiftData['work_start'],
                    'work_finish' => $shiftData['work_finish'],
                ]);

                $count++;
            }
        });

        $this->command->info("Updated {$count} tasks.");
    }
}
