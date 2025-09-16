<?php

namespace App\Jobs;

use App\Services\StatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        private ?StatsService $statsService = null
    ) {
        $this->onQueue('default');
        $this->statsService ??= app(StatsService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CalculateStatsJob: starting job execution');

        try {
            $this->statsService->markAsCalculating();

            if (!$this->statsService->hasEnoughData()) {
                Log::warning('CalculateStatsJob: Not enough data to calculate stats');
                $this->statsService->markAsCalculated();
                return;
            }

            $stats = $this->statsService->calculateStats();

            // Cache result
            $this->statsService->cacheStats($stats);

            $this->statsService->markAsCalculated();

            Log::info('CalculateStatsJob: Stats calculated and cached successfully', [
                'total_searches' => $stats['general']['total_searches'],
                'top_queries_count' => count($stats['top_queries']),
                'calculation_time' => microtime(true) - LARAVEL_START,
            ]);

        } catch (\Exception $e) {
            $this->statsService->markAsCalculated();

            Log::error('CalculateStatsJob: Error calculating stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        $this->statsService->markAsCalculated();

        Log::error('CalculateStatsJob: Job failed', [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);
    }
}
