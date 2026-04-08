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

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public int $tries = 1;

    public function __construct(
        public Report $report
    ) {}

    public function handle(): void
    {
        $this->report->update(['status' => Report::STATUS_PROCESSING]);

        try {
            ReportService::generate($this->report);
            $this->report->update(['status' => Report::STATUS_COMPLETED]);
        } catch (\Throwable $e) {
            $this->report->update(['status' => Report::STATUS_FAILED]);
            Log::error('Report generation failed', [
                'report_id' => $this->report->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
