<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_stats_with_data()
    {
        // Arrange
        $this->createSampleSearchData();
        app(\App\Services\StatsService::class)->forceRefresh();

        // Act
        $response = $this->getJson('/api/stats');

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'last_updated',
                         'general' => [
                             'total_searches',
                             'average_response_time',
                             'most_popular_hour'
                         ],
                         'top_queries',
                         'by_type' => [
                             'people',
                             'films'
                         ]
                     ]
                 ]);
    }

    public function test_stats_returns_correct_data_structure()
    {
        // Arrange
        Cache::flush();

        SearchQuery::create([
            'query' => 'vader',
            'type' => 'people',
            'results_count' => 1,
            'response_time_ms' => 100,
            'searched_at' => Carbon::now()
        ]);

        SearchQuery::create([
            'query' => 'empire',
            'type' => 'films',
            'results_count' => 1,
            'response_time_ms' => 200,
            'searched_at' => Carbon::now()
        ]);

        app(\App\Services\StatsService::class)->forceRefresh();

        // Act
        $response = $this->getJson('/api/stats');

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'general' => [
                             'total_searches' => 2
                         ],
                         'by_type' => [
                             'people' => [
                                 'total_searches' => 1
                             ],
                             'films' => [
                                 'total_searches' => 1
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_stats_without_data_shows_calculating_message()
    {
        // Arrange
        Cache::flush();

        // Act
        $response = $this->getJson('/api/stats');

        // Assert
        $response->assertStatus(202)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Stats are being calculated. Please try again in a few moments.',
                     'data' => null
                 ]);
    }

    public function test_stats_handles_errors_gracefully()
    {
        // Arrange
        $this->createSampleSearchData();
        $this->mock(\App\Services\StatsService::class, function ($mock) {
            $mock->shouldReceive('hasValidStats')->andReturn(true);
            $mock->shouldReceive('getStats')->andThrow(new \Exception('Database error'));
        });

        // Act
        $response = $this->getJson('/api/stats');

        // Assert
        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Internal server error'
                 ]);
    }

    private function createSampleSearchData()
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
            'response_time_ms' => 150,
            'searched_at' => Carbon::now()->hour(14)
        ]);

        SearchQuery::create([
            'query' => 'empire',
            'type' => 'films',
            'results_count' => 1,
            'response_time_ms' => 200,
            'searched_at' => Carbon::now()->hour(15)
        ]);
    }
}
