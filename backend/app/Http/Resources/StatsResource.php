<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'last_updated' => $this->resource['last_updated'],
            'general' => [
                'total_searches' => $this->resource['general']['total_searches'] ?? 0,
                'average_response_time' => $this->formatResponseTime($this->resource['general']['average_response_time'] ?? null),
                'most_popular_hour' => $this->formatHour($this->resource['general']['most_popular_hour'] ?? null),
            ],
            'top_queries' => $this->formatTopQueries($this->resource['top_queries'] ?? []),
            'by_type' => [
                'people' => $this->formatTypeStats($this->resource['by_type']['people'] ?? []),
                'films' => $this->formatTypeStats($this->resource['by_type']['films'] ?? []),
            ],
            'recent' => $this->when(
                isset($this->resource['recent']),
                fn() => $this->formatRecentStats($this->resource['recent'])
            ),
        ];
    }

    private function formatResponseTime(?float $time): array
    {
        if ($time === null) {
            return [
                'ms' => null,
                'formatted' => 'N/A',
            ];
        }

        return [
            'ms' => round($time, 2),
            'formatted' => round($time) . 'ms',
        ];
    }

    private function formatHour(?int $hour): array
    {
        if ($hour === null) {
            return [
                'hour' => null,
                'formatted' => 'N/A',
            ];
        }

        return [
            'hour' => $hour,
            'formatted' => sprintf('%02d:00h', $hour),
        ];
    }

    private function formatTopQueries(array $queries): array
    {
        return array_map(function ($query, $index) {
            $rank = $index + 1;
            return [
                'rank' => $rank,
                'query' => $query['query'],
                'type' => $query['type'],
                'count' => $query['count'],
                'percentage' => round($query['percentage'], 1),
            ];
        }, $queries, array_keys($queries));
    }

    private function formatTypeStats(array $stats): array
    {
        return [
            'total_searches' => $stats['total_searches'] ?? 0,
            'avg_response_time' => $this->formatResponseTime($stats['avg_response_time'] ?? null),
            'avg_results' => [
                'value' => $stats['avg_results'] ?? null,
                'formatted' => isset($stats['avg_results']) ? number_format($stats['avg_results'], 1) : 'N/A',
            ],
            'top_queries' => $stats['top_queries'] ?? [],
        ];
    }

    private function formatRecentStats(array $recent): array
    {
        $dailyBreakdown = array_map(function ($day) {
            return [
                'date' => $day['date'],
                'count' => $day['count'],
            ];
        }, $recent['daily_breakdown'] ?? []);

        return [
            'total_recent' => array_values($recent)[0] ?? 0,
            'avg_per_day' => [
                'value' => $recent['avg_per_day'] ?? 0,
                'formatted' => number_format($recent['avg_per_day'] ?? 0, 1) . ' per day',
            ],
            'daily_breakdown' => $dailyBreakdown,
        ];
    }
}
