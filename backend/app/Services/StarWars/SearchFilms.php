<?php

namespace App\Services\StarWars;

class SearchFilms implements \JsonSerializable
{
    public function __construct(
        public readonly array $films,
    ) {
    }

    public function jsonSerialize()
    {
        return array_map(fn($film) => [
            'id' => $film->id,
            'name' => $film->title,
        ], $this->films);
    }
}
