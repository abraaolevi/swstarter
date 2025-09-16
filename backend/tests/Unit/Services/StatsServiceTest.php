<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StatsService;
use App\Models\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatsService();
        Cache::flush();
    }

    public function test_calculate_stats_returns_correct_structure()
    {
        // Arrange
        $this->createSampleData();

        // Act
        $stats = $this->service->calculateStats();

        // Assert
        $this->assertArrayHasKey('general', $stats);
        $this->assertArrayHasKey('top_queries', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertArrayHasKey('recent', $stats);
        $this->assertArrayHasKey('last_updated', $stats);

        $this->assertArrayHasKey('total_searches', $stats['general']);
        $this->assertArrayHasKey('average_response_time', $stats['general']);
        $this->assertArrayHasKey('most_popular_hour', $stats['general']);
    }

    public function test_get_stats_by_type_filters_correctly()
    {
        // Arrange
        SearchQuery::create(['query' => 'luke', 'type' => 'people', 'results_count' => 1, 'response_time_ms' => 100, 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'vader', 'type' => 'people', 'results_count' => 1, 'response_time_ms' => 200, 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'empire', 'type' => 'films', 'results_count' => 1, 'response_time_ms' => 150, 'searched_at' => Carbon::now()]);

        // Act
        $peopleStats = $this->invokePrivateMethod('getStatsByType', ['people']);
        $filmsStats = $this->invokePrivateMethod('getStatsByType', ['films']);

        // Assert
        $this->assertEquals(2, $peopleStats['total_searches']);
        $this->assertEquals(1, $filmsStats['total_searches']);
        $this->assertEquals(150, $peopleStats['avg_response_time']);
        $this->assertEquals(150, $filmsStats['avg_response_time']);
    }

    public function test_get_recent_stats_calculates_correctly()
    {
        // Arrange
        SearchQuery::create(['query' => 'today1', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'today2', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'yesterday', 'type' => 'people', 'searched_at' => Carbon::now()->subDay()]);
        SearchQuery::create(['query' => 'old', 'type' => 'people', 'searched_at' => Carbon::now()->subDays(10)]);

        // Act
        $recentStats = $this->service->getRecentStats();

        // Assert
        $this->assertArrayHasKey('total_last_7_days', $recentStats);
        $this->assertArrayHasKey('avg_per_day', $recentStats);
        $this->assertArrayHasKey('daily_breakdown', $recentStats);

        $this->assertEquals(3, $recentStats['total_last_7_days']); // 3 dos últimos 7 dias
        $this->assertCount(7, $recentStats['daily_breakdown']); // 7 dias no breakdown
    }

    public function test_cache_stats_stores_in_cache()
    {
        // Arrange
        $testStats = [
            'general' => ['total_searches' => 5],
            'test' => true
        ];

        // Act
        $this->service->cacheStats($testStats);

        // Assert
        $cachedStats = Cache::get('stats_cache');
        $this->assertNotNull($cachedStats);
        $this->assertEquals($testStats, $cachedStats);
    }

    public function test_has_enough_data_returns_boolean()
    {
        // Act & Assert
        $this->assertFalse($this->service->hasEnoughData());

        // Arrange
        SearchQuery::create(['query' => 'test', 'type' => 'people', 'searched_at' => Carbon::now()]);

        // Act & Assert
        $this->assertTrue($this->service->hasEnoughData());
    }


    public function test_get_stats_returns_cached_when_available()
    {
        // Arrange
        $cachedStats = [
            'general' => ['total_searches' => 10],
            'cached' => true
        ];
        Cache::put('stats_cache', $cachedStats, 600);

        // Act
        $stats = $this->service->getStats();

        // Assert
        $this->assertEquals($cachedStats, $stats);
    }

    public function test_has_valid_stats_checks_cache_and_calculating_state()
    {
        // Act & Assert
        $this->assertFalse($this->service->hasValidStats());

        // Arrange
        Cache::put('stats_cache', ['test' => 'data'], 600);

        // Act & Assert
        $this->assertTrue($this->service->hasValidStats());
    }

    public function test_stats_calculation_handles_empty_database()
    {
        // Act
        $stats = $this->service->calculateStats();

        // Assert
        $this->assertEquals(0, $stats['general']['total_searches']);
        $this->assertEquals(0, $stats['general']['average_response_time']);
        $this->assertNull($stats['general']['most_popular_hour']);
        $this->assertEmpty($stats['top_queries']);
    }

    private function createSampleData()
    {
        SearchQuery::create([
            'query' => 'luke',
            'type' => 'people',
            'results_count' => 1,
            'response_time_ms' => 100,
            'searched_at' => Carbon::now()->hour(14)
        ]);

        SearchQuery::create([
            'query' => 'vader',
            'type' => 'people',
            'results_count' => 1,
            'response_time_ms' => 200,
            'searched_at' => Carbon::now()->hour(14)
        ]);

        SearchQuery::create([
            'query' => 'empire',
            'type' => 'films',
            'results_count' => 1,
            'response_time_ms' => 150,
            'searched_at' => Carbon::now()->hour(15)
        ]);
    }

    private function invokePrivateMethod(string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->service, $parameters);
    }
}
