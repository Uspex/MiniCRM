<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public int $tries = 1;
    public bool $failOnTimeout = true;

    public function __construct(
        public Report $report
    ) {}

    public function handle(): void
    {
        Log::info('Report generation started', [
            'report_id' => $this->report->id,
            'period'    => $this->report->date_from->format('d.m.Y') . ' - ' . $this->report->date_to->format('d.m.Y'),
        ]);

        $this->report->update(['status' => Report::STATUS_PROCESSING]);

        try {
            ReportService::generate($this->report);
            $this->report->update(['status' => Report::STATUS_COMPLETED]);

            Log::info('Report generation completed', [
                'report_id' => $this->report->id,
            ]);
        } catch (\Throwable $e) {
            $this->saveError($e);
        }
    }

    public function failed(?\Throwable $e): void
    {
        if ($e) {
            $this->saveError($e);
        }
    }

    private function saveError(\Throwable $e): void
    {
        $filename = 'reports/report_error_' . $this->report->id . '.txt';
        Storage::disk('local')->makeDirectory('reports');
        Storage::disk('local')->put($filename, implode("\n", [
            'Report #' . $this->report->id,
            'Date: ' . now()->format('d.m.Y H:i:s'),
            'Period: ' . $this->report->date_from->format('d.m.Y') . ' - ' . $this->report->date_to->format('d.m.Y'),
            '',
            'Error: ' . $e->getMessage(),
            '',
            'Stack trace:',
            $e->getTraceAsString(),
        ]));

        $this->report->update([
            'status'    => Report::STATUS_FAILED,
            'file_path' => $filename,
        ]);

        Log::error('Report generation failed', [
            'report_id' => $this->report->id,
            'error'     => $e->getMessage(),
        ]);
    }
}
