<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    const MAX_REPORTS = 5;

    public static function generate(int $userId, Carbon $dateFrom, Carbon $dateTo): Report
    {
        // Удаляем старые отчёты если превышен лимит
        self::cleanOldReports();

        $shifts = Setting::get(Setting::TYPE_SHIFTS, []);
        $dayStartHour = !empty($shifts) ? (int) explode(':', $shifts[0]['start'])[0] : 0;

        $filterFrom = $dateFrom->copy()->startOfDay()->addHours($dayStartHour);
        $filterTo   = $dateTo->copy()->endOfDay()->addHours($dayStartHour);

        // Все даты за период
        $days = $dateFrom->diffInDays($dateTo) + 1;
        $dates = collect();
        for ($i = 0; $i < $days; $i++) {
            $dates->push($dateFrom->copy()->addDays($i)->format('Y-m-d'));
        }

        // Запрос с группировкой по user_id + activity_id + date
        $rows = Task::select(
            'user_id',
            'activity_id',
            DB::raw('DATE(created_at - INTERVAL ' . $dayStartHour . ' HOUR) as date'),
            DB::raw('SUM(product_count) as total'),
            DB::raw('SUM(runtime) as total_runtime')
        )
            ->whereBetween('created_at', [$filterFrom, $filterTo])
            ->groupBy('user_id', 'activity_id', 'date')
            ->get();

        $userIds = $rows->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->orderBy('name')->get()->keyBy('id');

        $activityIds = $rows->pluck('activity_id')->unique();
        $activities = Activity::whereIn('id', $activityIds)->get()->keyBy('id');

        // Формируем CSV
        $filename = 'reports/report_' . now()->format('Ymd_His') . '.csv';
        Storage::disk('local')->makeDirectory('reports');

        $handle = fopen(Storage::disk('local')->path($filename), 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM для Excel

        // Заголовок
        $header = [__('report.csv.employee')];
        foreach ($dates as $date) {
            $header[] = Carbon::parse($date)->format('d.m');
        }
        $header[] = __('dashboard.table_total');
        fputcsv($handle, $header, ';');

        // Данные по сотрудникам
        foreach ($users as $user) {
            $row = [$user->name];
            $totalFact = 0;
            $totalPlan = 0;

            foreach ($dates as $date) {
                $fact = 0;
                $plan = 0;
                foreach ($activities as $activity) {
                    $taskRow = $rows->where('user_id', $user->id)
                        ->where('activity_id', $activity->id)
                        ->where('date', $date)
                        ->first();
                    if ($taskRow) {
                        $fact += (int) $taskRow->total;
                        if ($activity->plan_time && $taskRow->total_runtime) {
                            $plan += round(($taskRow->total_runtime * 3600) / $activity->plan_time);
                        }
                    }
                }

                $totalFact += $fact;
                $totalPlan += $plan;

                if ($plan && $fact) {
                    $coeff = round($fact / $plan, 2);
                    $row[] = $coeff . ' (' . $fact . '/' . $plan . ')';
                } elseif ($fact) {
                    $row[] = $fact;
                } else {
                    $row[] = '';
                }
            }

            // Итого
            if ($totalPlan && $totalFact) {
                $row[] = round($totalFact / $totalPlan, 2) . ' (' . $totalFact . '/' . $totalPlan . ')';
            } elseif ($totalFact) {
                $row[] = $totalFact;
            } else {
                $row[] = '';
            }

            fputcsv($handle, $row, ';');
        }

        fclose($handle);

        return Report::create([
            'user_id'   => $userId,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'file_path' => $filename,
        ]);
    }

    private static function cleanOldReports(): void
    {
        $count = Report::count();
        if ($count >= self::MAX_REPORTS) {
            $toDelete = Report::orderBy('created_at')->limit($count - self::MAX_REPORTS + 1)->get();
            foreach ($toDelete as $report) {
                Storage::disk('local')->delete($report->file_path);
                $report->delete();
            }
        }
    }
}
