<?php

namespace App\Services\StarWars;

class Person implements \JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $birthYear,
        public readonly string $gender,
        public readonly string $eyeColor,
        public readonly string $hairColor,
        public readonly string $height,
        public readonly string $mass,
        public readonly array $moviesIds,
    ) {
    }

    public static function fromJson(array $data): Person
    {
        $moviesIds = [];
        foreach ($data["properties"]["films"] as $film) {
            $moviesIds[] = (int) str_replace('https://www.swapi.tech/api/films/', '', $film);
        }

        return new Person(
            id: (int) $data['uid'],
            name: $data['properties']['name'],
            birthYear: $data['properties']['birth_year'],
            gender: $data['properties']['gender'],
            eyeColor: $data['properties']['eye_color'],
            hairColor: $data['properties']['hair_color'],
            height: $data['properties']['height'],
            mass: $data['properties']['mass'],
            moviesIds: $moviesIds,
        );
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_year' => $this->birthYear,
            'gender' => $this->gender,
            'eye_color' => $this->eyeColor,
            'hair_color' => $this->hairColor,
            'height' => $this->height,
            'mass' => $this->mass,
        ];
    }
}
