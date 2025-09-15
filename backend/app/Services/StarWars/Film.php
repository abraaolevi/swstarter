<?php

namespace App\Services\StarWars;

class Film implements \JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $openingCrawl,
        public readonly array $charactersIds,
    ) {
    }

    public static function fromJson(array $data): Film
    {
        $charactersIds = [];
        foreach ($data['properties']['characters'] as $item) {
            $charactersIds[] = (int) str_replace('https://www.swapi.tech/api/people/', '', $item);
        }

        return new Film(
            id: (int) $data['uid'],
            title: $data['properties']['title'],
            openingCrawl: $data['properties']['opening_crawl'],
            charactersIds: $charactersIds,
        );
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'opening_crawl' => $this->openingCrawl,
        ];
    }
}
