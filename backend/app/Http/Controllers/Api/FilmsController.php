<?php

namespace App\Http\Controllers\Api;

class FilmsController extends BaseSearchController
{
    protected function performSearch(string $query)
    {
        return $this->starWarsService->searchFilms($query);
    }

    protected function getDetailById(string $id)
    {
        return $this->starWarsService->getFilmDetail($id);
    }

    protected function getSearchType(): string
    {
        return 'films';
    }

    protected function getInvalidIdMessage(): string
    {
        return 'Invalid film ID';
    }

    protected function getNotFoundMessage(): string
    {
        return 'Film not found';
    }
}
