<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SearchQuery;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_search_query()
    {
        // Arrange and Act
        $searchQuery = SearchQuery::create([
            'query' => 'luke skywalker',
            'type' => 'people',
            'results_count' => 1,
            'response_time_ms' => 150.50,
            'user_ip' => '127.0.0.1',
            'user_agent' => ['browser' => 'Chrome'],
            'searched_at' => Carbon::now(),
        ]);

        // Assert
        $this->assertDatabaseHas('search_queries', [
            'query' => 'luke skywalker',
            'type' => 'people',
            'results_count' => 1,
        ]);

        $this->assertEquals('luke skywalker', $searchQuery->query);
        $this->assertEquals('people', $searchQuery->type);
        $this->assertEquals(1, $searchQuery->results_count);
    }

    public function test_scope_of_type_filters_correctly()
    {
        // Arrange
        SearchQuery::create(['query' => 'luke', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'vader', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'empire', 'type' => 'films', 'searched_at' => Carbon::now()]);

        // Act
        $peopleQueries = SearchQuery::ofType('people')->get();
        $filmQueries = SearchQuery::ofType('films')->get();

        // Assert
        $this->assertCount(2, $peopleQueries);
        $this->assertCount(1, $filmQueries);
        $this->assertEquals('people', $peopleQueries->first()->type);
        $this->assertEquals('films', $filmQueries->first()->type);
    }

    public function test_scope_last_days_filters_correctly()
    {
        // Arrange
        SearchQuery::create(['query' => 'recent', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'old', 'type' => 'people', 'searched_at' => Carbon::now()->subDays(10)]);

        // Act
        $recentQueries = SearchQuery::lastDays(7)->get();

        // Assert
        $this->assertCount(1, $recentQueries);
        $this->assertEquals('recent', $recentQueries->first()->query);
    }

    public function test_scope_popular_returns_correct_format()
    {
        // Arrange
        SearchQuery::create(['query' => 'luke', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'luke', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'vader', 'type' => 'people', 'searched_at' => Carbon::now()]);

        // Act
        $popularQueries = SearchQuery::popular(2)->get();

        // Assert
        $this->assertCount(2, $popularQueries);
        $this->assertEquals('luke', $popularQueries->first()->query);
        $this->assertEquals(2, $popularQueries->first()->search_count);
        $this->assertEquals('vader', $popularQueries->last()->query);
        $this->assertEquals(1, $popularQueries->last()->search_count);
    }

    public function test_get_general_stats_returns_correct_structure()
    {
        // Arrange
        SearchQuery::create([
            'query' => 'luke',
            'type' => 'people',
            'response_time_ms' => 100,
            'searched_at' => Carbon::now()->hour(14)
        ]);
        SearchQuery::create([
            'query' => 'vader',
            'type' => 'people',
            'response_time_ms' => 200,
            'searched_at' => Carbon::now()->hour(14)
        ]);

        // Act
        $stats = SearchQuery::getGeneralStats();

        // Assert
        $this->assertArrayHasKey('total_searches', $stats);
        $this->assertArrayHasKey('average_response_time', $stats);
        $this->assertArrayHasKey('most_popular_hour', $stats);

        $this->assertEquals(2, $stats['total_searches']);
        $this->assertEquals(150, $stats['average_response_time']);
        $this->assertEquals(14, $stats['most_popular_hour']);
    }

    public function test_get_top_queries_returns_correct_format()
    {
        // Arrange
        SearchQuery::create(['query' => 'luke', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'luke', 'type' => 'people', 'searched_at' => Carbon::now()]);
        SearchQuery::create(['query' => 'vader', 'type' => 'people', 'searched_at' => Carbon::now()]);

        // Act
        $topQueries = SearchQuery::getTopQueries(2);

        // Assert
        $this->assertCount(2, $topQueries);

        $firstQuery = $topQueries[0];
        $this->assertArrayHasKey('query', $firstQuery);
        $this->assertArrayHasKey('type', $firstQuery);
        $this->assertArrayHasKey('count', $firstQuery);
        $this->assertArrayHasKey('percentage', $firstQuery);

        $this->assertEquals('luke', $firstQuery['query']);
        $this->assertEquals(2, $firstQuery['count']);
        $this->assertEquals(66.67, $firstQuery['percentage']);
    }

    public function test_get_top_queries_returns_empty_when_no_data()
    {
        // Act
        $topQueries = SearchQuery::getTopQueries(5);

        // Assert
        $this->assertEmpty($topQueries);
        $this->assertIsArray($topQueries);
    }
}
