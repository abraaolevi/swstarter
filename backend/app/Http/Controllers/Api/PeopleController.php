<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StarWars\StarWarsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PeopleController extends Controller
{
    public function __construct(private StarWarsService $starWarsService)
    {
    }

    /**
     * Search for people by name
     * POST /api/people/search
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $query = strtolower(trim($request->input('query')));
            $results = $this->starWarsService->searchPeople($query);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get person details by ID
     * GET /api/people/{id}
     */
    public function show(string $id): JsonResponse
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid person ID'
            ], 400);
        }

        try {
            $person = $this->starWarsService->getPersonDetail($id);

            return response()->json([
                'success' => true,
                'data' => $person
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Person not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
