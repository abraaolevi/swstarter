<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SearchPeopleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_people_with_valid_query()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'name' => 'Luke Skywalker',
                            'birth_year' => '19BBY',
                            'gender' => 'male',
                            'eye_color' => 'blue',
                            'hair_color' => 'blond',
                            'height' => '172',
                            'mass' => '77',
                            'films' => ['https://www.swapi.tech/api/films/1']
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $response = $this->postJson('/api/people/search', [
            'query' => 'luke'
        ]);

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [
                             'id' => 1,
                             'name' => 'Luke Skywalker'
                         ]
                     ]
                 ]);
    }

    public function test_search_people_validates_required_query()
    {
        // Act
        $response = $this->postJson('/api/people/search', ['query' => '']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_search_people_validates_query_length()
    {
        // Act
        $response = $this->postJson('/api/people/search', [
            'query' => ''
        ]);

        // Assert
        $response->assertStatus(400)
                 ->assertJsonValidationErrors(['query']);
    }

    public function test_search_people_logs_query_to_database()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'name' => 'Luke Skywalker',
                            'birth_year' => '19BBY',
                            'gender' => 'male',
                            'eye_color' => 'blue',
                            'hair_color' => 'blond',
                            'height' => '172',
                            'mass' => '77',
                            'films' => []
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $response = $this->postJson('/api/people/search', ['query' => 'luke']);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'luke',
            'type' => 'people',
            'results_count' => 1,
        ]);

        $searchQuery = SearchQuery::first();
        $this->assertNotNull($searchQuery->response_time_ms);
        $this->assertGreaterThan(0, $searchQuery->response_time_ms);
    }

    public function test_search_people_handles_api_errors()
    {
        // Arrange
        Cache::flush();

        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([], 500)
        ]);

        // Act
        $response = $this->postJson('/api/people/search', [
            'query' => 'luke'
        ]);

        // Assert
        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Search failed'
                 ]);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'luke',
            'type' => 'people',
            'results_count' => 0,
        ]);
    }

    public function test_search_people_returns_correct_results_count()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'name' => 'Luke Skywalker',
                            'birth_year' => '19BBY',
                            'gender' => 'male',
                            'eye_color' => 'blue',
                            'hair_color' => 'blond',
                            'height' => '172',
                            'mass' => '77',
                            'films' => []
                        ]
                    ],
                    [
                        'uid' => '2',
                        'properties' => [
                            'name' => 'Luke Lars',
                            'birth_year' => '52BBY',
                            'gender' => 'male',
                            'eye_color' => 'blue',
                            'hair_color' => 'grey',
                            'height' => '178',
                            'mass' => '120',
                            'films' => []
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $response = $this->postJson('/api/people/search', [
            'query' => 'luke'
        ]);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'luke',
            'results_count' => 2,
        ]);
    }

    public function test_search_people_trims_and_lowercases_query()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response(['result' => []])
        ]);

        // Act
        $response = $this->postJson('/api/people/search', [
            'query' => '  LUKE  '
        ]);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('search_queries', [
            'query' => 'luke',
            'type' => 'people',
        ]);
    }
}
