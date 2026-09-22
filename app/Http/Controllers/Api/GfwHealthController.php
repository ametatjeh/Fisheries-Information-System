<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gfw\GfwApiService;
use Illuminate\Http\JsonResponse;

class GfwHealthController extends Controller
{
    public function __construct(
        protected GfwApiService $gfwService
    ) {}

    /**
     * Handle the incoming request to verify connectivity with GFW API v3.
     */
    public function __invoke(): JsonResponse
    {
        $health = $this->gfwService->checkHealth();

        $statusCode = $health['success'] ? 200 : 503;

        $response = [
            'success' => (bool) $health['success'],
            'source' => 'global_fishing_watch',
            'status' => (string) $health['status'],
        ];

        // Include user-friendly diagnostic message on failure without leaking credentials
        if (! $health['success'] && ! empty($health['message'])) {
            $response['message'] = $health['message'];
        }

        return response()->json($response, $statusCode);
    }
}
