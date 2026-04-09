<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    const MAX_REPORTS = 5;

    public static function generate(Report $report): void
    {
        $dateFrom = $report->date_from->copy()->startOfDay();
        $dateTo   = $report->date_to->copy()->endOfDay();

        $shifts = Setting::get(Setting::TYPE_SHIFTS, []);
        $dayStartHour = !empty($shifts) ? (int) explode(':', $shifts[0]['start'])[0] : 0;

        $filterFrom = $dateFrom->copy()->addHours($dayStartHour);
        $filterTo   = $dateTo->copy()->addHours($dayStartHour);

        // Все даты за период
        $days = $dateFrom->diffInDays($dateTo->copy()->startOfDay()) + 1;
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $dateFrom->copy()->addDays($i)->format('Y-m-d');
        }

        Log::info('Report: loading tasks data', [
            'report_id' => $report->id,
            'period'    => $filterFrom->format('Y-m-d H:i') . ' - ' . $filterTo->format('Y-m-d H:i'),
            'days'      => $days,
        ]);

        // Фильтры из отчёта
        $filters = $report->filters ?? [];
        $filterUserIds      = $filters['user_ids'] ?? [];
        $filterActivityIds  = $filters['activity_ids'] ?? [];
        $filterShifts       = $filters['shifts'] ?? [];
        $filterDepartments  = $filters['departments'] ?? [];

        // Собираем данные чанками: user_id => activity_id => date => {total, total_runtime}
        $data = [];
        $userIds = [];
        $activityIds = [];
        $chunkNumber = 0;

        $query = Task::select(
            'user_id',
            'activity_id',
            DB::raw('DATE(created_at - INTERVAL ' . $dayStartHour . ' HOUR) as date'),
            DB::raw('SUM(product_count) as total'),
            DB::raw('SUM(runtime) as total_runtime')
        )
            ->whereBetween('created_at', [$filterFrom, $filterTo])
            ->groupBy('user_id', 'activity_id', 'date')
            ->orderBy('user_id');

        if (!empty($filterUserIds)) {
            $query->whereIn('user_id', $filterUserIds);
        }

        if (!empty($filterActivityIds)) {
            $query->whereIn('activity_id', $filterActivityIds);
        }

        if (!empty($filterShifts)) {
            $query->whereIn('shift', $filterShifts);
        }

        if (!empty($filterDepartments)) {
            $departmentUserIds = User::whereIn('department', $filterDepartments)->pluck('id');
            $query->whereIn('user_id', $departmentUserIds);
        }

        $query->chunk(500, function ($rows) use (&$data, &$userIds, &$activityIds, &$chunkNumber, $report) {
                $chunkNumber++;
                Log::info('Report: processing chunk', [
                    'report_id' => $report->id,
                    'chunk'     => $chunkNumber,
                    'rows'      => $rows->count(),
                ]);

                foreach ($rows as $row) {
                    $userIds[$row->user_id] = true;
                    $activityIds[$row->activity_id] = true;
                    $data[$row->user_id][$row->activity_id][$row->date] = [
                        'total'         => (int) $row->total,
                        'total_runtime' => (float) $row->total_runtime,
                    ];
                }
            });

        Log::info('Report: tasks loaded', [
            'report_id'  => $report->id,
            'chunks'     => $chunkNumber,
            'users'      => count($userIds),
            'activities' => count($activityIds),
        ]);

        $users = User::whereIn('id', array_keys($userIds))->orderBy('name')->get()->keyBy('id');
        $activities = Activity::whereIn('id', array_keys($activityIds))->get()->keyBy('id');

        Log::info('Report: writing CSV', ['report_id' => $report->id, 'users' => $users->count()]);

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
        $header[] = __('report.csv.average');
        fputcsv($handle, $header, ';');

        // Данные по сотрудникам
        foreach ($users as $user) {
            $row = [$user->name];
            $dailyCoefficients = [];

            foreach ($dates as $date) {
                $fact = 0;
                $plan = 0;

                foreach ($activities as $activity) {
                    $taskData = $data[$user->id][$activity->id][$date] ?? null;
                    if ($taskData) {
                        $fact += $taskData['total'];
                        if ($activity->plan_time && $taskData['total_runtime']) {
                            $plan += round(($taskData['total_runtime'] * 3600) / $activity->plan_time);
                        }
                    }
                }

                if ($plan && $fact) {
                    $coefficient = round($fact / $plan, 2);
                    $dailyCoefficients[] = $coefficient;
                    $row[] = str_replace('.', ',', (string) $coefficient);
                } else {
                    $row[] = '';
                }
            }

            // Среднее значение
            if (!empty($dailyCoefficients)) {
                $row[] = str_replace('.', ',', (string) round(array_sum($dailyCoefficients) / count($dailyCoefficients), 2));
            } else {
                $row[] = '';
            }

            fputcsv($handle, $row, ';');
        }

        fclose($handle);

        $report->update(['file_path' => $filename]);
    }

    public static function cleanOldReports(): void
    {
        $count = Report::count();
        if ($count >= self::MAX_REPORTS) {
            $toDelete = Report::orderBy('created_at')->limit($count - self::MAX_REPORTS + 1)->get();
            foreach ($toDelete as $report) {
                if ($report->file_path) {
                    Storage::disk('local')->delete($report->file_path);
                }
                $report->delete();
            }
        }
    }
}
