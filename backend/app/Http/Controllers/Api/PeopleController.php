<?php

namespace App\Http\Controllers\Api;

class PeopleController extends BaseSearchController
{
    protected function performSearch(string $query)
    {
        return $this->starWarsService->searchPeople($query);
    }

    protected function getDetailById(string $id)
    {
        return $this->starWarsService->getPersonDetail($id);
    }

    protected function getSearchType(): string
    {
        return 'people';
    }

    protected function getInvalidIdMessage(): string
    {
        return 'Invalid person ID';
    }

    protected function getNotFoundMessage(): string
    {
        return 'Person not found';
    }
}
