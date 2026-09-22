<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Rzwp3k\Rzwp3kSpatialAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Rzwp3kSpatialAnalysisController extends Controller
{
    public const CACHE_TTL = 1800; // 30 minutes

    public function __construct(
        protected Rzwp3kSpatialAnalysisService $analysisService
    ) {}

    /**
     * Spatial intersection analysis between Fishing Grounds and RZWP3K Zones.
     */
    public function fishingGrounds(Request $request): JsonResponse
    {
        $fgId = $request->query('fishing_ground_id') ? (int) $request->query('fishing_ground_id') : null;
        $zoneType = $request->query('zone_type');
        $cleanZoneType = in_array(strtoupper((string) $zoneType), ['KPU', 'KK', 'AL', 'KSNT'], true)
            ? strtoupper((string) $zoneType)
            : null;

        $cacheKey = 'rzwp3k:spatial:fg:'.md5(json_encode([
            'fg_id' => $fgId,
            'zone_type' => $cleanZoneType,
        ]));

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($fgId, $cleanZoneType) {
            return $this->analysisService->analyzeFishingGrounds($fgId, $cleanZoneType);
        });

        return response()->json($result, 200);
    }

    /**
     * Spatial observation analysis between GFW Activities / Events and RZWP3K Zones.
     */
    public function gfwActivities(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', '2026-09-01');
        $endDate = $request->query('end_date', '2026-09-07');

        // Validate Date Format YYYY-MM-DD
        $dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (! is_string($startDate) || ! preg_match($dateRegex, $startDate) ||
            ! is_string($endDate) || ! preg_match($dateRegex, $endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus YYYY-MM-DD.',
                'status' => 422,
            ], 422);
        }

        // Validate Calendar Validity
        [$sY, $sM, $sD] = explode('-', $startDate);
        [$eY, $eM, $eD] = explode('-', $endDate);

        if (! checkdate((int) $sM, (int) $sD, (int) $sY) || ! checkdate((int) $eM, (int) $eD, (int) $eY)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
                'status' => 422,
            ], 422);
        }

        // Validate start <= end
        if ($startDate > $endDate) {
            return response()->json([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
                'status' => 422,
            ], 422);
        }

        // Validate Maximum 7 Days Range
        $sTime = strtotime($startDate);
        $eTime = strtotime($endDate);
        $diffDays = (int) round(($eTime - $sTime) / 86400) + 1;

        if ($diffDays > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang tanggal tidak boleh lebih dari 7 hari.',
                'status' => 422,
            ], 422);
        }

        // Validate Limit (1-100)
        $rawLimit = $request->query('limit');
        if ($rawLimit !== null) {
            if (! is_numeric($rawLimit) || (int) $rawLimit < 1 || (int) $rawLimit > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter limit harus berupa angka antara 1 dan 100.',
                    'status' => 422,
                ], 422);
            }
            $limit = (int) $rawLimit;
        } else {
            $limit = 50;
        }

        // Validate Offset (>= 0)
        $rawOffset = $request->query('offset');
        if ($rawOffset !== null) {
            if (! is_numeric($rawOffset) || (int) $rawOffset < 0 || ((string) (int) $rawOffset !== (string) $rawOffset && ! ctype_digit((string) $rawOffset))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter offset harus berupa angka non-negatif.',
                    'status' => 422,
                ], 422);
            }
            $offset = (int) $rawOffset;
        } else {
            // Check Page parameter
            $rawPage = $request->query('page');
            if ($rawPage !== null) {
                if (! is_numeric($rawPage) || (int) $rawPage < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Parameter page harus berupa angka minimal 1.',
                        'status' => 422,
                    ], 422);
                }
                $offset = ((int) $rawPage - 1) * $limit;
            } else {
                $offset = 0;
            }
        }

        // Zone Filters
        $zoneType = $request->query('zone_type');
        $cleanZoneType = in_array(strtoupper((string) $zoneType), ['KPU', 'KK', 'AL', 'KSNT'], true)
            ? strtoupper((string) $zoneType)
            : null;

        $rawZoneId = $request->query('zone_id');
        $zoneId = ($rawZoneId !== null && is_numeric($rawZoneId) && (int) $rawZoneId > 0)
            ? (int) $rawZoneId
            : null;

        $cacheKey = 'rzwp3k:spatial:gfw:v3:'.md5(json_encode([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'offset' => $offset,
            'zone_type' => $cleanZoneType,
            'zone_id' => $zoneId,
        ]));

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate, $limit, $offset, $cleanZoneType, $zoneId) {
            return $this->analysisService->analyzeGfwEvents($startDate, $endDate, [
                'limit' => $limit,
                'offset' => $offset,
                'zone_type' => $cleanZoneType,
                'zone_id' => $zoneId,
            ]);
        });

        $statusCode = $result['status'] ?? ($result['success'] ? 200 : 500);

        return response()->json($result, $statusCode);
    }
}
