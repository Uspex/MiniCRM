<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Report;
use App\Models\Setting;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:' . Permission::PERMISSION_REPORT_LIST, only: ['index']),
            new Middleware('permission:' . Permission::PERMISSION_REPORT_GENERATE, only: ['generate']),
        ];
    }

    public function index(): View
    {
        $reports = Report::with('user')
            ->orderByDesc('created_at')
            ->limit(ReportService::MAX_REPORTS)
            ->get();

        $canViewAll = auth()->user()->can(Permission::PERMISSION_REPORT_ALL_USERS);
        $allUsers = $canViewAll ? User::orderBy('name')->get() : collect();
        $allActivities = Activity::orderBy('name')->get();

        $shifts = Setting::get(Setting::TYPE_SHIFTS, []);
        $allShifts = collect($shifts)->map(fn($s) => [
            'id'   => (int) $s['shift'],
            'name' => $s['name'],
        ]);

        $allDepartments = collect(Setting::get(Setting::TYPE_DEPARTMENTS, []))
            ->pluck('name')
            ->filter()
            ->values();

        return view('admin.report.index', compact(
            'reports', 'canViewAll',
            'allUsers', 'allActivities', 'allShifts', 'allDepartments',
        ) + ['maxReports' => ReportService::MAX_REPORTS]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'date_from'     => ['required', 'date_format:d.m.Y'],
            'date_to'       => ['required', 'date_format:d.m.Y'],
            'user_id'       => ['nullable', 'array'],
            'user_id.*'     => ['integer', 'exists:users,id'],
            'activity_id'   => ['nullable', 'array'],
            'activity_id.*' => ['integer', 'exists:activities,id'],
            'shift'         => ['nullable', 'array'],
            'department'    => ['nullable', 'array'],
        ]);

        $canViewAll = auth()->user()->can(Permission::PERMISSION_REPORT_ALL_USERS);

        $filters = [
            'user_ids'     => $canViewAll ? array_map('intval', array_filter((array) $request->input('user_id', []))) : [auth()->id()],
            'activity_ids' => array_map('intval', array_filter((array) $request->input('activity_id', []))),
            'shifts'       => array_map('intval', array_filter((array) $request->input('shift', []))),
            'departments'  => array_filter((array) $request->input('department', [])),
        ];

        ReportService::cleanOldReports();

        $report = Report::create([
            'user_id'   => auth()->id(),
            'date_from' => Carbon::createFromFormat('d.m.Y', $request->date_from)->startOfDay(),
            'date_to'   => Carbon::createFromFormat('d.m.Y', $request->date_to)->endOfDay(),
            'filters'   => $filters,
            'status'    => Report::STATUS_PENDING,
        ]);

        GenerateReportJob::dispatch($report);

        return redirect()
            ->route('admin.report.index')
            ->with('success', __('report.generate_queued'));
    }

    public function statuses(): JsonResponse
    {
        $reports = Report::orderByDesc('created_at')
            ->limit(ReportService::MAX_REPORTS)
            ->get(['id', 'status', 'file_path']);

        return response()->json($reports->map(fn($r) => [
            'id'           => $r->id,
            'status'       => $r->status,
            'status_badge' => match ($r->status) {
                Report::STATUS_PENDING    => '<span class="badge bg-warning">' . __('report.status.pending') . '</span>',
                Report::STATUS_PROCESSING => '<span class="badge bg-info">' . __('report.status.processing') . '</span>',
                Report::STATUS_COMPLETED  => '<span class="badge bg-success">' . __('report.status.completed') . '</span>',
                Report::STATUS_FAILED     => '<span class="badge bg-danger">' . __('report.status.failed') . '</span>',
                default                   => '',
            },
            'download_url' => ($r->status === Report::STATUS_COMPLETED || ($r->status === Report::STATUS_FAILED && $r->file_path))
                ? route('admin.report.download', $r->id) : null,
            'is_failed'    => $r->status === Report::STATUS_FAILED && $r->file_path,
            'can_delete'   => $r->status !== Report::STATUS_PROCESSING,
        ]));
    }

    public function download(int $id)
    {
        $report = Report::findOrFail($id);

        if (!in_array($report->status, [Report::STATUS_COMPLETED, Report::STATUS_FAILED]) || !$report->file_path) {
            return redirect()
                ->route('admin.report.index')
                ->with('error', __('report.file_not_found'));
        }

        $path = Storage::disk('local')->path($report->file_path);

        if (!file_exists($path)) {
            return redirect()
                ->route('admin.report.index')
                ->with('error', __('report.file_not_found'));
        }

        $filename = 'Report_'
            . $report->date_from->format('d.m.Y') . '-'
            . $report->date_to->format('d.m.Y') . '.csv';

        return response()->download($path, $filename);
    }

    public function destroy(int $id): RedirectResponse
    {
        $report = Report::findOrFail($id);

        if ($report->file_path) {
            Storage::disk('local')->delete($report->file_path);
        }

        $report->delete();

        return redirect()
            ->route('admin.report.index')
            ->with('success', __('common.delete.success'));
    }
}
