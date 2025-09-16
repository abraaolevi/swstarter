<?php

namespace App\Services;

use App\Jobs\CalculateStatsJob;
use App\Models\SearchQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StatsService
{
    private const string CACHE_KEY = 'stats_cache';
    private const string CALCULATING_KEY = 'stats_cache_calculating';
    private const int CACHE_TTL = 600;
    private const int CALCULATING_TTL = 300;
    private const int TOP_QUERIES_LIMIT = 5;
    private const int RECENT_DAYS = 7;

    public function __construct()
    {
    }

    public function getStats(): ?array
    {
        return Cache::get(self::CACHE_KEY);
    }

    public function hasValidStats(): bool
    {
        return Cache::has(self::CACHE_KEY);
    }

    public function refreshStats(): void
    {
        if (!$this->isJobAlreadyQueued()) {
            CalculateStatsJob::dispatch();
            Log::info('StatsService: Job for update status dispatch');
        } else {
            Log::info('StatsService: Job already exists in the queue');
        }
    }

    private function isJobAlreadyQueued(): bool
    {
        return Cache::has(self::CALCULATING_KEY);
    }

    public function markAsCalculating(): void
    {
        Cache::put(self::CALCULATING_KEY, true, self::CALCULATING_TTL);
    }

    public function markAsCalculated(): void
    {
        Cache::forget(self::CALCULATING_KEY);
    }

    public function calculateStats(): array
    {
        try {
            $generalStats = SearchQuery::getGeneralStats();
            $topQueries = SearchQuery::getTopQueries(self::TOP_QUERIES_LIMIT);
            $recentStats = $this->getRecentStats();

            return [
                'general' => $generalStats,
                'top_queries' => $topQueries,
                'by_type' => [
                    'people' => $this->getStatsByType('people'),
                    'films' => $this->getStatsByType('films'),
                ],
                'recent' => $recentStats,
                'last_updated' => Carbon::now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('StatsService: Error calculating stats', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getStatsByType(string $type): array
    {
        $queries = SearchQuery::ofType($type);

        return [
            'total_searches' => $queries->count(),
            'avg_response_time' => round($queries->avg('response_time_ms') ?? 0, 2),
            'avg_results' => round($queries->avg('results_count') ?? 0, 2),
            'top_queries' => SearchQuery::ofType($type)->popular(3)->get()->map(function ($item) {
                return [
                    'query' => $item->query,
                    'count' => $item->search_count,
                ];
            })->toArray(),
        ];
    }

    public function getRecentStats(): array
    {
        $days = self::RECENT_DAYS;
        $recentQueries = SearchQuery::lastDays($days);

        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = SearchQuery::whereDate('searched_at', $date)->count();

            $dailyStats[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ];
        }

        return [
            "total_last_{$days}_days" => $recentQueries->count(),
            'avg_per_day' => round($recentQueries->count() / $days, 2),
            'daily_breakdown' => $dailyStats,
        ];
    }

    public function cacheStats(array $stats): void
    {
        Cache::put(self::CACHE_KEY, $stats, self::CACHE_TTL);
        Log::info('StatsService: Stats cached', [
            'total_searches' => $stats['general']['total_searches'] ?? 0,
        ]);
    }

    public function hasEnoughData(): bool
    {
        return SearchQuery::count() > 0;
    }

    public function forceRefresh(): array
    {
        $stats = $this->calculateStats();
        $this->cacheStats($stats);

        $this->refreshStats();

        return $stats;
    }
}
