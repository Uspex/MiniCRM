<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use App\Models\Permission;
use App\Models\Report;
use App\Services\ReportService;
use Carbon\Carbon;
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

        return view('admin.report.index', [
            'reports'    => $reports,
            'maxReports' => ReportService::MAX_REPORTS,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'date_from' => ['required', 'date_format:d.m.Y'],
            'date_to'   => ['required', 'date_format:d.m.Y'],
        ]);

        ReportService::cleanOldReports();

        $report = Report::create([
            'user_id'   => auth()->id(),
            'date_from' => Carbon::createFromFormat('d.m.Y', $request->date_from)->startOfDay(),
            'date_to'   => Carbon::createFromFormat('d.m.Y', $request->date_to)->endOfDay(),
            'status'    => Report::STATUS_PENDING,
        ]);

        GenerateReportJob::dispatch($report);

        return redirect()
            ->route('admin.report.index')
            ->with('success', __('report.generate_queued'));
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
