<?php

namespace App\Http\Controllers;

use App\Services\Gfw\AoiService;
use App\Services\Gfw\GfwFishingGroundSpatialAnalysisService;
use App\Services\GFWService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GFWController extends Controller
{
    /**
     * Test connection and authentication with the Global Fishing Watch (GFW) API.
     */
    public function test(GFWService $gfw): JsonResponse
    {
        $result = $gfw->testConnection();

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * Retrieve the validated AOI for ZEE Indonesia di Kawasan Aceh with optional 100 NM buffer.
     */
    public function zeeIndonesiaAcehAoi(Request $request, AoiService $aoiService): JsonResponse
    {
        try {
            $isBuffer100Nm = $request->query('buffer') === '100nm' || $request->boolean('buffer_100nm');

            if ($isBuffer100Nm) {
                $summary = $aoiService->getZeeIndonesiaAcehBuffer100NmSummary();

                $payload = [
                    'success' => true,
                    'name' => $summary['name'],
                    'geometry_type' => $summary['geometry_type'],
                    'crs' => $summary['crs'],
                    'feature_count' => $summary['feature_count'],
                    'buffer_distance_meters' => $summary['buffer_distance_meters'],
                    'buffer_distance_km' => $summary['buffer_distance_km'],
                    'buffer_distance_nm' => $summary['buffer_distance_nm'],
                ];

                if ($request->boolean('geojson')) {
                    $payload['geojson'] = $aoiService->getZeeIndonesiaAcehBuffer100NmGeometry();
                }

                return response()->json($payload, 200);
            }

            $summary = $aoiService->getZeeIndonesiaAcehSummary();

            $payload = [
                'success' => true,
                'name' => $summary['name'],
                'geometry_type' => $summary['geometry_type'],
                'crs' => $summary['crs'],
                'feature_count' => $summary['feature_count'],
            ];

            if ($request->boolean('geojson')) {
                $payload['geojson'] = $aoiService->getZeeIndonesiaAcehGeometry();
            }

            return response()->json($payload, 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca AOI ZEE Indonesia - Kawasan Aceh: '.$e->getMessage(),
            ], 404);
        }
    }

    /**
     * Retrieve the validated AOI for ZEE Aceh (legacy/regional endpoint).
     */
    public function zeeAcehAoi(AoiService $aoiService): JsonResponse
    {
        try {
            $summary = $aoiService->getSummary();

            return response()->json([
                'success' => true,
                'name' => $summary['name'],
                'geometry_type' => $summary['geometry_type'],
                'crs' => $summary['crs'],
                'feature_count' => $summary['feature_count'],
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca AOI ZEE Aceh: '.$e->getMessage(),
            ], 404);
        }
    }

    /**
     * Query fishing events from GFW API v3 filtered by ZEE Indonesia di Kawasan Aceh AOI.
     */
    public function eventsZeeIndonesiaAceh(Request $request, GFWService $gfw, AoiService $aoiService): JsonResponse
    {
        $startDateStr = $request->query('start_date', '2026-09-01');
        $endDateStr = $request->query('end_date', '2026-09-07');

        // 1. Validate date format (YYYY-MM-DD)
        if (! is_string($startDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDateStr)
            || ! is_string($endDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateStr)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus berformat YYYY-MM-DD.',
            ], 422);
        }

        // 2. Validate calendar date validity
        [$sYear, $sMonth, $sDay] = explode('-', $startDateStr);
        [$eYear, $eMonth, $eDay] = explode('-', $endDateStr);

        if (! checkdate((int) $sMonth, (int) $sDay, (int) $sYear) || ! checkdate((int) $eMonth, (int) $eDay, (int) $eYear)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDateStr)->startOfDay();
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        // 3. Validate chronological order
        if ($startDate->gt($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ], 422);
        }

        // 4. Validate max range of 7 calendar days (inclusive)
        $inclusiveDays = (int) $startDate->diffInDays($endDate) + 1;
        if ($inclusiveDays > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Date range cannot exceed 7 days during GFW-03/GFW-04 testing.',
            ], 422);
        }

        // 5. Validate limit parameter (1 to 100)
        $limit = 50;
        if ($request->has('limit')) {
            $rawLimit = (string) $request->query('limit');
            if (! ctype_digit($rawLimit) || (int) $rawLimit < 1 || (int) $rawLimit > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter limit harus berupa bilangan bulat antara 1 dan 100.',
                ], 422);
            }
            $limit = (int) $rawLimit;
        }

        // 6. Validate pagination parameter (offset / page)
        $offset = 0;
        if ($request->has('page')) {
            $rawPage = (string) $request->query('page');
            if (! ctype_digit($rawPage) || (int) $rawPage < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter page harus berupa bilangan bulat positif >= 1.',
                ], 422);
            }
            $offset = ((int) $rawPage - 1) * $limit;
        } elseif ($request->has('offset')) {
            $rawOffset = (string) $request->query('offset');
            if (! ctype_digit($rawOffset) || (int) $rawOffset < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter offset harus berupa bilangan bulat >= 0.',
                ], 422);
            }
            $offset = (int) $rawOffset;
        }

        // 7. Load AOI Geometry
        try {
            $geometryData = $aoiService->getZeeIndonesiaAcehGeometry();
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat AOI ZEE Indonesia Kawasan Aceh: '.$e->getMessage(),
            ], 500);
        }

        // 8. Execute GFW Events Query
        $result = $gfw->getEvents(
            $geometryData,
            $startDateStr,
            $endDateStr,
            [
                'aoi_name' => 'ZEE Indonesia - Kawasan Aceh',
                'limit' => $limit,
                'offset' => $offset,
            ]
        );

        $httpStatus = $result['status'] ?? ($result['success'] ? 200 : 500);
        unset($result['status']);

        return response()->json($result, $httpStatus);
    }

    /**
     * Query vessels from GFW API filtered by ZEE Indonesia di Kawasan Aceh AOI.
     */
    public function vesselsZeeIndonesiaAceh(Request $request, GFWService $gfw, AoiService $aoiService): JsonResponse
    {
        $startDateStr = $request->query('start', $request->query('start_date', '2026-09-01'));
        $endDateStr = $request->query('end', $request->query('end_date', '2026-09-07'));

        // 1. Validate date format (YYYY-MM-DD)
        if (! is_string($startDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDateStr)
            || ! is_string($endDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateStr)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus berformat YYYY-MM-DD.',
            ], 422);
        }

        // 2. Validate calendar date validity
        [$sYear, $sMonth, $sDay] = explode('-', $startDateStr);
        [$eYear, $eMonth, $eDay] = explode('-', $endDateStr);

        if (! checkdate((int) $sMonth, (int) $sDay, (int) $sYear) || ! checkdate((int) $eMonth, (int) $eDay, (int) $eYear)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDateStr)->startOfDay();
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        // 3. Validate chronological order
        if ($startDate->gt($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ], 422);
        }

        // 4. Validate max range of 7 calendar days (inclusive)
        $inclusiveDays = (int) $startDate->diffInDays($endDate) + 1;
        if ($inclusiveDays > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Date range cannot exceed 7 days during GFW-03/GFW-04 testing.',
            ], 422);
        }

        // 5. Validate limit parameter (1 to 100)
        $limit = 50;
        if ($request->has('limit')) {
            $rawLimit = (string) $request->query('limit');
            if (! ctype_digit($rawLimit) || (int) $rawLimit < 1 || (int) $rawLimit > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter limit harus berupa bilangan bulat antara 1 dan 100.',
                ], 422);
            }
            $limit = (int) $rawLimit;
        }

        // 6. Validate pagination parameter (offset / page)
        $offset = 0;
        if ($request->has('page')) {
            $rawPage = (string) $request->query('page');
            if (! ctype_digit($rawPage) || (int) $rawPage < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter page harus berupa bilangan bulat positif >= 1.',
                ], 422);
            }
            $offset = ((int) $rawPage - 1) * $limit;
        } elseif ($request->has('offset')) {
            $rawOffset = (string) $request->query('offset');
            if (! ctype_digit($rawOffset) || (int) $rawOffset < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter offset harus berupa bilangan bulat >= 0.',
                ], 422);
            }
            $offset = (int) $rawOffset;
        }

        // 7. Load AOI Geometry
        try {
            $geometryData = $aoiService->getZeeIndonesiaAcehGeometry();
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'AOI unavailable: Gagal memuat AOI ZEE Indonesia Kawasan Aceh: '.$e->getMessage(),
            ], 500);
        }

        // 8. Execute GFW Vessels in AOI Query
        $result = $gfw->getVesselsInAoi(
            $geometryData,
            $startDateStr,
            $endDateStr,
            [
                'limit' => $limit,
                'offset' => $offset,
                'vessel_type' => $request->query('vessel_type'),
                'flag' => $request->query('flag'),
                'activity' => $request->query('activity'),
                'search' => $request->query('search', $request->query('query')),
            ]
        );

        $httpStatus = $result['status'] ?? ($result['success'] ? 200 : 500);
        unset($result['status']);

        return response()->json($result, $httpStatus);
    }

    /**
     * Spatial overlay analysis between GFW Events and Master Fishing Grounds in Aceh.
     */
    public function spatialFishingGrounds(Request $request, GfwFishingGroundSpatialAnalysisService $spatialService): JsonResponse
    {
        $startDateStr = $request->query('start_date', '2026-09-01');
        $endDateStr = $request->query('end_date', '2026-09-07');

        // 1. Validate date format (YYYY-MM-DD)
        if (! is_string($startDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDateStr)
            || ! is_string($endDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateStr)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus berformat YYYY-MM-DD.',
            ], 422);
        }

        // 2. Validate calendar date validity
        [$sYear, $sMonth, $sDay] = explode('-', $startDateStr);
        [$eYear, $eMonth, $eDay] = explode('-', $endDateStr);

        if (! checkdate((int) $sMonth, (int) $sDay, (int) $sYear) || ! checkdate((int) $eMonth, (int) $eDay, (int) $eYear)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDateStr)->startOfDay();
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        // 3. Validate chronological order
        if ($startDate->gt($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ], 422);
        }

        // 4. Validate max range of 7 calendar days (inclusive)
        $inclusiveDays = (int) $startDate->diffInDays($endDate) + 1;
        if ($inclusiveDays > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang tanggal tidak boleh melebihi 7 hari.',
            ], 422);
        }

        // 5. Validate limit parameter (1 to 100)
        $limit = 50;
        if ($request->has('limit')) {
            $rawLimit = (string) $request->query('limit');
            if (! ctype_digit($rawLimit) || (int) $rawLimit < 1 || (int) $rawLimit > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter limit harus berupa bilangan bulat antara 1 dan 100.',
                ], 422);
            }
            $limit = (int) $rawLimit;
        }

        // 6. Validate pagination parameter (offset / page)
        $offset = 0;
        if ($request->has('page')) {
            $rawPage = (string) $request->query('page');
            if (! ctype_digit($rawPage) || (int) $rawPage < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter page harus berupa bilangan bulat positif >= 1.',
                ], 422);
            }
            $offset = ((int) $rawPage - 1) * $limit;
        } elseif ($request->has('offset')) {
            $rawOffset = (string) $request->query('offset');
            if (! ctype_digit($rawOffset) || (int) $rawOffset < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter offset harus berupa bilangan bulat >= 0.',
                ], 422);
            }
            $offset = (int) $rawOffset;
        }

        // 7. Validate optional fishing_ground_id
        $fishingGroundId = null;
        if ($request->filled('fishing_ground_id')) {
            $rawFgId = (string) $request->query('fishing_ground_id');
            if (! ctype_digit($rawFgId) || (int) $rawFgId < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter fishing_ground_id harus berupa bilangan bulat positif.',
                ], 422);
            }
            $fishingGroundId = (int) $rawFgId;
        }

        // 8. Execute Spatial Overlay Analysis
        $result = $spatialService->analyze(
            $startDateStr,
            $endDateStr,
            [
                'limit' => $limit,
                'offset' => $offset,
                'fishing_ground_id' => $fishingGroundId,
            ]
        );

        $httpStatus = $result['status'] ?? ($result['success'] ? 200 : 500);
        unset($result['status']);

        return response()->json($result, $httpStatus);
    }

    /**
     * Query vessel movement track points & line string.
     */
    public function vesselTrack(string $vesselId, Request $request, GFWService $gfw): JsonResponse
    {
        $cleanId = trim($vesselId);
        if ($cleanId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vessel ID tidak boleh kosong.',
            ], 422);
        }

        $startDateStr = $request->query('start_date', $request->query('start', now()->subDays(6)->toDateString()));
        $endDateStr = $request->query('end_date', $request->query('end', now()->toDateString()));

        // 1. Validate date format (YYYY-MM-DD)
        if (! is_string($startDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDateStr)
            || ! is_string($endDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateStr)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus berformat YYYY-MM-DD.',
            ], 422);
        }

        // 2. Validate calendar date validity
        [$sYear, $sMonth, $sDay] = explode('-', $startDateStr);
        [$eYear, $eMonth, $eDay] = explode('-', $endDateStr);

        if (! checkdate((int) $sMonth, (int) $sDay, (int) $sYear) || ! checkdate((int) $eMonth, (int) $eDay, (int) $eYear)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDateStr)->startOfDay();
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        // 3. Validate chronological order
        if ($startDate->gt($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ], 422);
        }

        // 4. Validate max range of 7 calendar days
        $inclusiveDays = (int) $startDate->diffInDays($endDate) + 1;
        if ($inclusiveDays > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Date range cannot exceed 7 days during GFW-03/GFW-04 testing.',
            ], 422);
        }

        $result = $gfw->getVesselTrack($cleanId, $startDateStr, $endDateStr);
        $status = $result['status'] ?? ($result['success'] ? 200 : 500);
        unset($result['status']);

        return response()->json($result, $status);
    }

    /**
     * Query operational dashboard summary with consolidated KPIs, vessels, events, and alerts.
     */
    public function dashboardSummary(Request $request, GFWService $gfw, AoiService $aoiService): JsonResponse
    {
        $startDateStr = $request->query('start_date', $request->query('start', now()->subDays(6)->toDateString()));
        $endDateStr = $request->query('end_date', $request->query('end', now()->toDateString()));

        // 1. Validate date format (YYYY-MM-DD)
        if (! is_string($startDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDateStr)
            || ! is_string($endDateStr) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDateStr)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus berformat YYYY-MM-DD.',
            ], 422);
        }

        // 2. Validate calendar date validity
        [$sYear, $sMonth, $sDay] = explode('-', $startDateStr);
        [$eYear, $eMonth, $eDay] = explode('-', $endDateStr);

        if (! checkdate((int) $sMonth, (int) $sDay, (int) $sYear) || ! checkdate((int) $eMonth, (int) $eDay, (int) $eYear)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDateStr)->startOfDay();
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ], 422);
        }

        // 3. Validate chronological order
        if ($startDate->gt($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ], 422);
        }

        // 4. Validate max range of 7 calendar days
        $inclusiveDays = (int) $startDate->diffInDays($endDate) + 1;
        if ($inclusiveDays > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang tanggal tidak boleh melebihi 7 hari.',
            ], 422);
        }

        // Load AOI Geometry
        try {
            $geometryData = $aoiService->getZeeIndonesiaAcehGeometry();
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'AOI unavailable: Gagal memuat AOI ZEE Indonesia Kawasan Aceh: '.$e->getMessage(),
            ], 500);
        }

        $limit = max(1, min(100, (int) $request->query('limit', 50)));
        $offset = max(0, (int) $request->query('offset', 0));

        $result = $gfw->getDashboardData(
            $geometryData,
            $startDateStr,
            $endDateStr,
            [
                'limit' => $limit,
                'offset' => $offset,
                'vessel_type' => $request->query('vessel_type'),
                'flag' => $request->query('flag'),
                'activity' => $request->query('activity'),
                'search' => $request->query('search', $request->query('query')),
            ]
        );

        $status = $result['status'] ?? ($result['success'] ? 200 : 500);
        unset($result['status']);

        return response()->json($result, $status);
    }
}
