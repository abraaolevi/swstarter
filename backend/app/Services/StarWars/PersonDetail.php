<?php

namespace App\Services\StarWars;

class PersonDetail
{
    public function __construct(
        public readonly Person $person,
        public readonly array $films,
    ) {
    }
}
