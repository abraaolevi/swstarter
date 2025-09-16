<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'type',
        'results_count',
        'response_time_ms',
        'user_ip',
        'user_agent',
        'searched_at',
    ];

    protected $casts = [
        'user_agent' => 'array',
        'searched_at' => 'datetime',
    ];

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePopular(Builder $query, int $limit = 5): Builder
    {
        return $query->selectRaw('query, type, COUNT(*) as search_count')
            ->groupBy('query', 'type')
            ->orderByDesc('search_count')
            ->limit($limit);
    }

    public function scopeLastDays(Builder $query, int $days = 7): Builder
    {
        return $query->where('searched_at', '>=', Carbon::now()->subDays($days));
    }


    public static function getGeneralStats(): array
    {
        $totalSearches = self::count();
        $avgResponseTime = self::avg('response_time_ms');

        $popularHour = self::selectRaw('EXTRACT(HOUR FROM searched_at) as hour, COUNT(*) as count')
            ->groupByRaw('EXTRACT(HOUR FROM searched_at)')
            ->orderByDesc('count')
            ->first();

        return [
            'total_searches' => $totalSearches,
            'average_response_time' => round($avgResponseTime ?? 0, 2),
            'most_popular_hour' => $popularHour ? (int) $popularHour->hour : null,
        ];
    }

    public static function getTopQueries(int $limit = 5): array
    {
        $total = self::count();

        if ($total === 0) {
            return [];
        }

        return self::popular($limit)
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'query' => $item->query,
                    'type' => $item->type,
                    'count' => $item->search_count,
                    'percentage' => round(($item->search_count / $total) * 100, 2),
                ];
            })
            ->toArray();
    }
}
