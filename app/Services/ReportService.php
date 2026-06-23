<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskHistory;
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
            Report::TYPE_COEFFICIENT         => self::generateCoefficient($report),
            Report::TYPE_PRODUCTIVITY        => self::generateProductivity($report),
            Report::TYPE_OPERATIONS          => self::generateOperations($report),
            Report::TYPE_OPERATIONS_HISTORY  => self::generateOperationsHistory($report),
            Report::TYPE_OPERATIONS_CANCELLED => self::generateOperationsCancelled($report),
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
        $header[] = __('report.csv.total');
        fputcsv($handle, $header, ';');

        foreach ($users as $user) {
            $row = [$user->name];
            $totalFact = 0;
            $totalPlan = 0;

            foreach ($dates as $date) {
                $fact = 0;
                $plan = 0;

                foreach ($activities as $activity) {
                    $taskData = $data[$user->id][$activity->id][$date] ?? null;
                    if ($taskData) {
                        $fact += $taskData['total'];
                        if ($activity->plan_time && $taskData['total_runtime']) {
                            $plan += round(($taskData['total_runtime'] * 3600) / $activity->plan_time);
                        } elseif (!$activity->plan_time) {
                            $plan += $taskData['total'];
                        }
                    }
                }

                $totalFact += $fact;
                $totalPlan += $plan;

                if ($plan && $fact) {
                    $row[] = str_replace('.', ',', (string) round($fact / $plan, 2));
                } else {
                    $row[] = '';
                }
            }

            if ($totalPlan && $totalFact) {
                $row[] = str_replace('.', ',', (string) round($totalFact / $totalPlan, 2));
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
            ->reportable()
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

    private static function generateOperationsHistory(Report $report): void
    {
        [$dateFrom, $dateTo] = self::resolveDateRange($report);

        $filters = $report->filters ?? [];
        $filterUserIds      = $filters['user_ids'] ?? [];
        $filterActivityIds  = $filters['activity_ids'] ?? [];
        $filterShifts       = $filters['shifts'] ?? [];
        $filterDepartments  = $filters['departments'] ?? [];

        $query = TaskHistory::with([
                'editor:id,name',
                'task:id,user_id,activity_id',
                'task.user:id,name,department',
                'task.activity:id,name',
            ])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereHas('task', function ($q) use ($filterUserIds, $filterActivityIds, $filterShifts, $filterDepartments) {
                if (!empty($filterUserIds)) {
                    $q->whereIn('user_id', $filterUserIds);
                }
                if (!empty($filterActivityIds)) {
                    $q->whereIn('activity_id', $filterActivityIds);
                }
                if (!empty($filterShifts)) {
                    $q->whereIn('shift', $filterShifts);
                }
                if (!empty($filterDepartments)) {
                    $departmentUserIds = User::whereIn('department', $filterDepartments)->pluck('id');
                    $q->whereIn('user_id', $departmentUserIds);
                }
            })
            ->orderBy('created_at');

        $shiftNames = collect(Setting::get(Setting::TYPE_SHIFTS, []))
            ->mapWithKeys(fn($s) => [(int) $s['shift'] => $s['name']])
            ->all();

        $activityNames = Activity::pluck('name', 'id')->all();

        $fieldLabels = [
            'activity_id'   => __('task.form.fields.activity_id'),
            'product_count' => __('task.form.fields.product_count'),
            'runtime'       => __('task.form.fields.runtime'),
            'shift'         => __('task.form.fields.shift'),
            'work_day'      => __('task.form.fields.work_day'),
            'message'       => __('task.form.fields.message'),
        ];

        [$handle, $filename] = self::openCsv($report);

        fputcsv($handle, [
            __('report.csv.changed_at'),
            __('report.csv.editor'),
            __('report.csv.operation_id'),
            __('report.csv.employee'),
            __('report.csv.department'),
            __('report.csv.activity'),
            __('report.csv.field'),
            __('report.csv.old_value'),
            __('report.csv.new_value'),
        ], ';');

        $chunkNumber = 0;
        $query->chunk(500, function ($rows) use ($handle, $shiftNames, $activityNames, $fieldLabels, &$chunkNumber, $report) {
            $chunkNumber++;
            Log::info('Report: processing chunk', [
                'report_id' => $report->id,
                'chunk'     => $chunkNumber,
                'rows'      => $rows->count(),
            ]);

            foreach ($rows as $history) {
                $task = $history->task;

                foreach (($history->changes ?? []) as $field => $pair) {
                    fputcsv($handle, [
                        optional($history->created_at)->format('d.m.Y H:i'),
                        $history->editor->name ?? '',
                        $history->task_id,
                        $task->user->name ?? '',
                        $task->user->department ?? '',
                        $task->activity->name ?? '',
                        $fieldLabels[$field] ?? $field,
                        self::formatHistoryValue($field, $pair['old'] ?? null, $shiftNames, $activityNames),
                        self::formatHistoryValue($field, $pair['new'] ?? null, $shiftNames, $activityNames),
                    ], ';');
                }
            }
        });

        self::closeCsv($handle, $report, $filename);
    }

    private static function generateOperationsCancelled(Report $report): void
    {
        [$dateFrom, $dateTo] = self::resolveDateRange($report);

        $filters = $report->filters ?? [];
        $filterUserIds      = $filters['user_ids'] ?? [];
        $filterActivityIds  = $filters['activity_ids'] ?? [];
        $filterShifts       = $filters['shifts'] ?? [];
        $filterDepartments  = $filters['departments'] ?? [];

        $query = Task::with([
                'user:id,name,department',
                'activity:id,name',
                'cancelRequester:id,name',
                'cancelProcessor:id,name',
            ])
            ->where('cancel_status', Task::CANCEL_CANCELLED)
            ->whereBetween('work_day', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->orderBy('work_day')
            ->orderBy('cancel_processed_at');

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
            __('report.csv.work_day'),
            __('report.csv.shift'),
            __('report.csv.employee'),
            __('report.csv.department'),
            __('report.csv.activity'),
            __('report.csv.product_count'),
            __('report.csv.runtime'),
            __('report.csv.cancel_reason'),
            __('report.csv.cancel_requester'),
            __('report.csv.cancel_approver'),
            __('report.csv.cancel_approved_at'),
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
                    $task->work_day ? Carbon::parse($task->work_day)->format('d.m.Y') : '',
                    $shiftLabel,
                    $task->user->name ?? '',
                    $task->user->department ?? '',
                    $task->activity->name ?? '',
                    $task->product_count,
                    $task->runtime !== null ? str_replace('.', ',', (string) $task->runtime) : '',
                    $task->cancel_reason,
                    $task->cancelRequester->name ?? '',
                    $task->cancelProcessor->name ?? '',
                    optional($task->cancel_processed_at)->format('d.m.Y H:i'),
                ], ';');
            }
        });

        self::closeCsv($handle, $report, $filename);
    }

    /**
     * Приводит значение изменённого поля к читаемому виду для CSV.
     */
    private static function formatHistoryValue(string $field, $value, array $shiftNames, array $activityNames): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match ($field) {
            'activity_id' => $activityNames[(int) $value] ?? (string) $value,
            'shift'       => $shiftNames[(int) $value] ?? (string) $value,
            'work_day'    => Carbon::parse($value)->format('d.m.Y'),
            'runtime'     => str_replace('.', ',', (string) $value),
            default       => (string) $value,
        };
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
            ->reportable()
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
