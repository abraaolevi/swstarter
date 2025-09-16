<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SearchQuery;
use App\Services\StarWars\StarWarsService;
use App\Services\StarWars\SearchPeople;
use App\Services\StarWars\SearchFilms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

abstract class BaseSearchController extends Controller
{
    public function __construct(protected StarWarsService $starWarsService)
    {
    }

    public function search(Request $request): JsonResponse
    {
        $validator = $this->validateSearchRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $startTime = microtime(true);
        $query = strtolower(trim($request->input('query')));

        try {
            $results = $this->performSearch($query);

            $responseTime = $this->calculateResponseTime($startTime);
            $resultsCount = $this->countResults($results);

            $this->logSearchQuery($query, $responseTime, $resultsCount, $request);

            return $this->successResponse($results);
        } catch (\Exception $e) {
            $responseTime = $this->calculateResponseTime($startTime);
            $this->logSearchQuery($query, $responseTime, 0, $request, true);

            return $this->errorResponse($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return response()->json([
                'success' => false,
                'message' => $this->getInvalidIdMessage()
            ], 400);
        }

        try {
            $result = $this->getDetailById($id);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $this->getNotFoundMessage(),
                'error' => $e->getMessage()
            ], 404);
        }
    }

    protected function validateSearchRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'query' => 'required|string|min:1|max:100'
        ]);
    }

    protected function calculateResponseTime(float $startTime): float
    {
        return round((microtime(true) - $startTime) * 1000, 2);
    }

    protected function countResults($results): int
    {
        if (is_array($results)) {
            return count($results);
        }
        if ($results instanceof LengthAwarePaginator) {
            return $results->count();
        }
        if ($results instanceof SearchPeople) {
            return count($results->people);
        }
        if ($results instanceof SearchFilms) {
            return count($results->films);
        }
        return 0;
    }

    protected function isValidId(string $id): bool
    {
        return is_numeric($id) && $id > 0;
    }

    protected function logSearchQuery(string $query, float $responseTime, int $resultsCount, Request $request, bool $hasError = false): void
    {
        SearchQuery::create([
            'query' => $query,
            'type' => $this->getSearchType(),
            'results_count' => $resultsCount,
            'response_time_ms' => $responseTime,
            'user_ip' => $request->ip(),
            'user_agent' => $this->formatUserAgent($request, $hasError),
            'searched_at' => Carbon::now(),
        ]);
    }

    protected function formatUserAgent(Request $request, bool $hasError = false): ?array
    {
        $userAgent = $request->header('User-Agent');

        if (!$userAgent) {
            return null;
        }

        $result = ['string' => $userAgent];

        if ($hasError) {
            $result['error'] = true;
        }

        return $result;
    }

    protected function successResponse($results): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    protected function errorResponse(\Exception $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Search failed',
            'error' => $e->getMessage()
        ], 500);
    }


    abstract protected function performSearch(string $query);
    abstract protected function getDetailById(string $id);
    abstract protected function getSearchType(): string;
    abstract protected function getInvalidIdMessage(): string;
    abstract protected function getNotFoundMessage(): string;
}
