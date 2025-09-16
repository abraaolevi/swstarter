<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SearchFilmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_films_with_valid_query()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/films*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'title' => 'A New Hope',
                            'opening_crawl' => 'It is a period of civil war...',
                            'characters' => ['https://www.swapi.tech/api/people/1']
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $response = $this->postJson('/api/films/search', ['query' => 'hope']);

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [
                             'id' => 1,
                             'name' => 'A New Hope'
                         ]
                     ]
                 ]);
    }

    public function test_search_films_validates_required_query()
    {
        // Act
        $response = $this->postJson('/api/films/search', []);

        // Assert
        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false
                 ])
                 ->assertJsonValidationErrors(['query']);
    }

    public function test_search_films_logs_query_to_database()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/films*' => Http::response([
                'result' => [
                    [
                        'uid' => '2',
                        'properties' => [
                            'title' => 'The Empire Strikes Back',
                            'opening_crawl' => 'It is a dark time for the Rebellion...',
                            'characters' => []
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $response = $this->postJson('/api/films/search', [
            'query' => 'empire'
        ]);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'empire',
            'type' => 'films',
            'results_count' => 1,
        ]);

        $searchQuery = SearchQuery::first();
        $this->assertNotNull($searchQuery->response_time_ms);
        $this->assertGreaterThan(0, $searchQuery->response_time_ms);
    }

    public function test_search_films_handles_empty_results()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/films*' => Http::response([
                'result' => []
            ])
        ]);

        // Act
        $response = $this->postJson('/api/films/search', [
            'query' => 'nonexistent'
        ]);

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => []
                 ]);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'nonexistent',
            'type' => 'films',
            'results_count' => 0,
        ]);
    }

    public function test_search_films_handles_api_errors()
    {
        // Arrange
        Cache::flush();

        Http::fake([
            'https://www.swapi.tech/api/films*' => Http::response([], 500)
        ]);

        // Act
        $response = $this->postJson('/api/films/search', [
            'query' => 'empire'
        ]);

        // Assert
        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Search failed'
                 ]);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'empire',
            'type' => 'films',
            'results_count' => 0,
        ]);
    }
}
