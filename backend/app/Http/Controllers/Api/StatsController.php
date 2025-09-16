<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatsResource;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function __construct(
        private StatsService $statsService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            if (!$this->statsService->hasValidStats()) {
                $this->statsService->refreshStats();

                return response()->json([
                    'success' => false,
                    'message' => 'Stats are being calculated. Please try again in a few moments.',
                    'data' => null,
                ], 202);
            }

            $stats = $this->statsService->getStats();

            return response()->json([
                'success' => true,
                'message' => 'Stats retrieved successfully.',
                'data' => new StatsResource($stats),
            ]);

        } catch (\Exception $e) {
            Log::error('StatsController: stats error', [
                'error' => $e->getMessage(),
                'user_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
