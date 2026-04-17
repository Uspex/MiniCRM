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
        $type = $report->type ?? Report::TYPE_COEFFICIENT;

        Log::info('Report: generate', [
            'report_id' => $report->id,
            'type'      => $type,
            'period'    => $report->date_from->format('Y-m-d') . ' - ' . $report->date_to->format('Y-m-d'),
        ]);

        match ($type) {
            Report::TYPE_COEFFICIENT  => self::generateCoefficient($report),
            Report::TYPE_PRODUCTIVITY => self::generateProductivity($report),
            Report::TYPE_OPERATIONS   => self::generateOperations($report),
        };
    }

    private static function generateCoefficient(Report $report): void
    {
        [$dateFrom, $dateTo, $dates] = self::resolveDateRange($report);

        $data = [];
        $userIds = [];
        $activityIds = [];

        $query = self::buildAggregatedQuery($report, $dateFrom, $dateTo);
        $chunkNumber = 0;

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

        $users = User::whereIn('id', array_keys($userIds))->orderBy('name')->get()->keyBy('id');
        $activities = Activity::whereIn('id', array_keys($activityIds))->get()->keyBy('id');

        [$handle, $filename] = self::openCsv($report);

        $header = [__('report.csv.employee')];
        foreach ($dates as $date) {
            $header[] = Carbon::parse($date)->format('d.m');
        }
        $header[] = __('report.csv.average');
        fputcsv($handle, $header, ';');

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

            if (!empty($dailyCoefficients)) {
                $row[] = str_replace('.', ',', (string) round(array_sum($dailyCoefficients) / count($dailyCoefficients), 2));
            } else {
                $row[] = '';
            }

            fputcsv($handle, $row, ';');
        }

        self::closeCsv($handle, $report, $filename);
    }

    private static function generateProductivity(Report $report): void
    {
        [$dateFrom, $dateTo, $dates] = self::resolveDateRange($report);

        $data = [];
        $userIds = [];
        $activityIds = [];

        $query = self::buildAggregatedQuery($report, $dateFrom, $dateTo);
        $chunkNumber = 0;

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
                $data[$row->user_id][$row->activity_id][$row->date] = (int) $row->total;
            }
        });

        $users = User::whereIn('id', array_keys($userIds))->orderBy('name')->get()->keyBy('id');
        $activities = Activity::whereIn('id', array_keys($activityIds))->orderBy('name')->get()->keyBy('id');

        [$handle, $filename] = self::openCsv($report);

        $header = [__('report.csv.employee'), __('report.csv.activity')];
        foreach ($dates as $date) {
            $header[] = Carbon::parse($date)->format('d.m');
        }
        $header[] = __('report.csv.total');
        fputcsv($handle, $header, ';');

        foreach ($users as $user) {
            foreach ($activities as $activity) {
                $hasAny = false;
                $row = [$user->name, $activity->name];
                $total = 0;

                foreach ($dates as $date) {
                    $value = $data[$user->id][$activity->id][$date] ?? null;
                    if ($value !== null) {
                        $hasAny = true;
                        $total += $value;
                        $row[] = $value;
                    } else {
                        $row[] = '';
                    }
                }

                if (!$hasAny) {
                    continue;
                }

                $row[] = $total;
                fputcsv($handle, $row, ';');
            }
        }

        self::closeCsv($handle, $report, $filename);
    }

    private static function generateOperations(Report $report): void
    {
        [$dateFrom, $dateTo] = self::resolveDateRange($report);

        $filters = $report->filters ?? [];
        $filterUserIds      = $filters['user_ids'] ?? [];
        $filterActivityIds  = $filters['activity_ids'] ?? [];
        $filterShifts       = $filters['shifts'] ?? [];
        $filterDepartments  = $filters['departments'] ?? [];

        $query = Task::with(['user:id,name,department', 'activity:id,name'])
            ->whereBetween('work_day', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->orderBy('work_day')
            ->orderBy('created_at');

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

        $shiftNames = collect(Setting::get(Setting::TYPE_SHIFTS, []))
            ->mapWithKeys(fn($s) => [(int) $s['shift'] => $s['name']])
            ->all();

        [$handle, $filename] = self::openCsv($report);

        fputcsv($handle, [
            __('report.csv.created_at'),
            __('report.csv.work_day'),
            __('report.csv.shift'),
            __('report.csv.employee'),
            __('report.csv.department'),
            __('report.csv.activity'),
            __('report.csv.product_count'),
            __('report.csv.runtime'),
            __('report.csv.message'),
        ], ';');

        $chunkNumber = 0;
        $query->chunk(500, function ($rows) use ($handle, $shiftNames, &$chunkNumber, $report) {
            $chunkNumber++;
            Log::info('Report: processing chunk', [
                'report_id' => $report->id,
                'chunk'     => $chunkNumber,
                'rows'      => $rows->count(),
            ]);

            foreach ($rows as $task) {
                $shiftLabel = $task->shift ? ($shiftNames[(int) $task->shift] ?? '') : '';

                fputcsv($handle, [
                    optional($task->created_at)->format('d.m.Y H:i'),
                    $task->work_day ? Carbon::parse($task->work_day)->format('d.m.Y') : '',
                    $shiftLabel,
                    $task->user->name ?? '',
                    $task->user->department ?? '',
                    $task->activity->name ?? '',
                    $task->product_count,
                    $task->runtime !== null ? str_replace('.', ',', (string) $task->runtime) : '',
                    $task->message,
                ], ';');
            }
        });

        self::closeCsv($handle, $report, $filename);
    }

    private static function resolveDateRange(Report $report): array
    {
        $dateFrom = $report->date_from->copy();
        $dateTo   = $report->date_to->copy();

        $days = $dateFrom->diffInDays($dateTo) + 1;
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $dateFrom->copy()->addDays($i)->format('Y-m-d');
        }

        return [$dateFrom, $dateTo, $dates];
    }

    private static function buildAggregatedQuery(Report $report, Carbon $dateFrom, Carbon $dateTo)
    {
        $filters = $report->filters ?? [];
        $filterUserIds      = $filters['user_ids'] ?? [];
        $filterActivityIds  = $filters['activity_ids'] ?? [];
        $filterShifts       = $filters['shifts'] ?? [];
        $filterDepartments  = $filters['departments'] ?? [];

        $query = Task::select(
            'user_id',
            'activity_id',
            'work_day as date',
            DB::raw('SUM(product_count) as total'),
            DB::raw('SUM(runtime) as total_runtime')
        )
            ->whereBetween('work_day', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->groupBy('user_id', 'activity_id', 'work_day')
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

        return $query;
    }

    private static function openCsv(Report $report): array
    {
        $filename = 'reports/report_' . now()->format('Ymd_His') . '_' . $report->id . '.csv';
        Storage::disk('local')->makeDirectory('reports');

        $handle = fopen(Storage::disk('local')->path($filename), 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        return [$handle, $filename];
    }

    private static function closeCsv($handle, Report $report, string $filename): void
    {
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
