<?php

namespace App\Services\StarWars;

class SearchPeople implements \JsonSerializable
{
    public function __construct(
        public readonly array $people,
    ) {
    }

    public function jsonSerialize()
    {
        return array_map(fn($person) => [
            'id' => $person->id,
            'name' => $person->name,
        ], $this->people);
    }
}
