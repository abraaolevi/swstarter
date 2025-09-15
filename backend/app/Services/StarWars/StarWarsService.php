<?php

namespace App\Services\StarWars;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

const ONE_HOUR_IN_SECONDS = 3600;

class StarWarsService
{
    private const string BASE_URL = 'https://www.swapi.tech/api';
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = Http::baseUrl(self::BASE_URL)
            ->timeout(10)
            ->retry(3, 100);
    }

    public function searchPeople(string $query): SearchPeople
    {
        $searchPeopleCacheKey = $this->cacheKeyForSearchPeople($query);
        $searchResult = Cache::get($searchPeopleCacheKey);

        if ($searchResult) {
            return $searchResult;
        }

        $response = $this->httpClient->get('/people', ['name' => $query]);

        if (!$response->successful()) {
            Log::warning("People search failed", ['query' => $query, 'status' => $response->status()]);
            return new SearchPeople([]);
        }

        $data = $response->json();

        $people = [];
        foreach ($data['result'] as $item) {
            $person = Person::fromJson($item);

            $personCacheKey = $this->cacheKeyForPerson($person->id);
            Cache::put($personCacheKey, $person, ONE_HOUR_IN_SECONDS);

            $people[] = $person;
        }

        $searchResult = new SearchPeople($people);
        Cache::put($searchPeopleCacheKey, $searchResult, ONE_HOUR_IN_SECONDS);

        return $searchResult;
    }

    public function searchFilms(string $query): SearchFilms
    {
        $searchFilmsCacheKey = $this->cacheKeyForSearchFilms($query);
        $searchResult = Cache::get($searchFilmsCacheKey);

        if ($searchResult) {
            return $searchResult;
        }

        $response = $this->httpClient->get('/films', ['title' => $query]);

        if (!$response->successful()) {
            Log::warning("Films search failed", ['query' => $query, 'status' => $response->status()]);
            return new SearchFilms([]);
        }

        $data = $response->json();

        $films = [];
        foreach ($data['result'] as $item) {
            $film = Film::fromJson($item);

            $filmCacheKey = $this->cacheKeyForFilm($film->id);
            Cache::put($filmCacheKey, $film, ONE_HOUR_IN_SECONDS);

            $films[] = $film;
        }

        $searchResult = new SearchFilms($films);
        Cache::put($searchFilmsCacheKey, $searchResult, ONE_HOUR_IN_SECONDS);

        return $searchResult;
    }

    public function getPerson(string $id): Person
    {
        $personCacheKey = $this->cacheKeyForPerson($id);
        $person = Cache::get($personCacheKey);
        if ($person) {
            return $person;
        }

        $response = $this->httpClient->get("/people/{$id}");

        if (!$response->successful()) {
            Log::warning("Person details failed", ['id' => $id, 'status' => $response->status()]);
            throw new \Exception("Person not found");
        }

        $person = Person::fromJson($response->json()['result']);

        Cache::put($personCacheKey, $person, ONE_HOUR_IN_SECONDS);
        return $person;
    }

    public function getPersonDetail(string $id): PersonDetail
    {
        $person = $this->getPerson($id);

        $relatedFilms = [];
        foreach ($person->moviesIds as $filmId) {
            $filmCacheKey = $this->cacheKeyForFilm($filmId);
            $film = Cache::get($filmCacheKey);
            if (!$film) {
                $film = $this->getFilm((string) $filmId);
            }
            $relatedFilms[] = $film;
        }

        return new PersonDetail(person: $person, films: $relatedFilms);
    }

    public function getFilm(string $id): Film
    {
        $filmCacheKey = $this->cacheKeyForFilm($id);
        $film = Cache::get($filmCacheKey);
        if ($film) {
            return $film;
        }

        $response = $this->httpClient->get("/films/{$id}");

        if (!$response->successful()) {
            Log::warning("Film details failed", ['id' => $id, 'status' => $response->status()]);
            throw new \Exception("Film not found");
        }

        $film = Film::fromJson($response->json()['result']);
        Cache::put($filmCacheKey, $film, ONE_HOUR_IN_SECONDS);

        return $film;
    }

    public function getFilmDetail(string $id): FilmDetail
    {
        $film = $this->getFilm($id);

        $relatedCharacters = [];
        foreach ($film->charactersIds as $characterId) {
            $characterCacheKey = $this->cacheKeyForPerson($characterId);
            $character = Cache::get($characterCacheKey);
            if (!$character) {
                $character = $this->getPerson((string) $characterId);
            }
            $relatedCharacters[] = $character;
        }

        return new FilmDetail(film: $film, characters: $relatedCharacters);
    }

    //
    // Private methods
    //

    private function cacheKeyForSearchPeople(string $query): string
    {
        return "search_people_" . md5(strtolower($query));
    }

    private function cacheKeyForPerson(int $id): string
    {
        return "person_{$id}";
    }

    private function cacheKeyForSearchFilms(string $query): string
    {
        return "search_films_" . md5(strtolower($query));
    }

    private function cacheKeyForFilm(int $id): string
    {
        return "film_{$id}";
    }
}
