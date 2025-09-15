<?php

namespace App\Services\StarWars;

class FilmDetail
{
    public function __construct(
        public readonly Film $film,
        public readonly array $characters,
    ) {
    }
}
