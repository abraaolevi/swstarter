<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StarWars\StarWarsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FilmsController extends Controller
{
    public function __construct(private StarWarsService $starWarsService)
    {
    }

    /**
     * Search for films by title
     * POST /api/films/search
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
            $results = $this->starWarsService->searchFilms($query);

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
     * Get film details by ID
     * GET /api/films/{id}
     */
    public function show(string $id): JsonResponse
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid film ID'
            ], 400);
        }

        try {
            $film = $this->starWarsService->getFilmDetail($id);

            return response()->json([
                'success' => true,
                'data' => $film
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Film not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
