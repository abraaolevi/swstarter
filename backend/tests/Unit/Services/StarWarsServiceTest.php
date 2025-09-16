<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StarWars\StarWarsService;
use App\Services\StarWars\SearchPeople;
use App\Services\StarWars\SearchFilms;
use App\Services\StarWars\Person;
use App\Services\StarWars\Film;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class StarWarsServiceTest extends TestCase
{
    protected StarWarsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StarWarsService();
        Cache::flush();
    }

    public function test_search_people_returns_search_people_object()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'name' => 'Luke Skywalker',
                            'films' => ['https://www.swapi.tech/api/films/1']
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $result = $this->service->searchPeople('luke');

        // Assert
        $this->assertInstanceOf(SearchPeople::class, $result);
        $this->assertCount(1, $result->people);
        $this->assertEquals('Luke Skywalker', $result->people[0]->name);
    }

    public function test_search_people_uses_cache()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'name' => 'Luke Skywalker',
                            'films' => []
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $result1 = $this->service->searchPeople('luke');
        $result2 = $this->service->searchPeople('luke');

        // Assert
        $this->assertEquals($result1, $result2);
        Http::assertSentCount(1);
    }

    public function test_search_films_returns_search_films_object()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/films*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'title' => 'A New Hope',
                            'characters' => ['https://www.swapi.tech/api/people/1']
                        ]
                    ]
                ]
            ])
        ]);

        // Act
        $result = $this->service->searchFilms('hope');

        // Assert
        $this->assertInstanceOf(SearchFilms::class, $result);
        $this->assertCount(1, $result->films);
        $this->assertEquals('A New Hope', $result->films[0]->title);
    }

    public function test_get_person_by_id_returns_person()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people/1' => Http::response([
                'result' => [
                    'uid' => '1',
                    'properties' => [
                        'name' => 'Luke Skywalker',
                        'films' => ['https://www.swapi.tech/api/films/1']
                    ]
                ]
            ])
        ]);

        // Act
        $result = $this->service->getPerson('1');

        // Assert
        $this->assertInstanceOf(Person::class, $result);
        $this->assertEquals('Luke Skywalker', $result->name);
        $this->assertEquals(1, $result->id);
    }

    public function test_get_person_by_id_handles_not_found()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people/999' => Http::response([], 404)
        ]);

        // Assert
        $this->expectException(\Exception::class);

        // Act
        $this->service->getPerson('999');
    }

    public function test_get_film_by_id_returns_film()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/films/1' => Http::response([
                'result' => [
                    'uid' => '1',
                    'properties' => [
                        'title' => 'A New Hope',
                        'characters' => ['https://www.swapi.tech/api/people/1']
                    ]
                ]
            ])
        ]);

        // Act
        $result = $this->service->getFilm('1');

        // Assert
        $this->assertInstanceOf(Film::class, $result);
        $this->assertEquals('A New Hope', $result->title);
        $this->assertEquals(1, $result->id);
    }

    public function test_get_film_by_id_handles_not_found()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/films/999' => Http::response([], 404)
        ]);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Film not found');

        // Act
        $this->service->getFilm('999');
    }

    public function test_cache_keys_are_consistent()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response(['result' => []]),
            'https://www.swapi.tech/api/films*' => Http::response(['result' => []])
        ]);

        // Act
        $this->service->searchPeople('luke');
        $this->service->searchPeople('LUKE');
        $this->service->searchFilms('empire');
        $this->service->searchFilms('EMPIRE');

        // Assert
        Http::assertSentCount(2);
    }

    public function test_search_caches_individual_items()
    {
        // Arrange
        Http::fake([
            'https://www.swapi.tech/api/people*' => Http::response([
                'result' => [
                    [
                        'uid' => '1',
                        'properties' => [
                            'name' => 'Luke Skywalker',
                            'films' => []
                        ]
                    ]
                ]
            ]),
            'https://www.swapi.tech/api/people/1' => Http::response([
                'result' => [
                    'uid' => '1',
                    'properties' => [
                        'name' => 'Luke Skywalker',
                        'films' => []
                    ]
                ]
            ])
        ]);

        // Act
        $this->service->searchPeople('luke');
        $person = $this->service->getPerson('1');

        // Assert
        $this->assertEquals('Luke Skywalker', $person->name);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/people?name=luke');
        });

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/people/1');
        });
    }
}
