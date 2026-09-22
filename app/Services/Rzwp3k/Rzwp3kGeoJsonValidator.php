<?php

namespace App\Services\Rzwp3k;

class Rzwp3kGeoJsonValidator
{
    /**
     * Validasi dataset atau berkas GeoJSON sesuai standar RFC 7946 WGS84 [lng, lat].
     *
     * @param  array<string, mixed>|string  $input
     * @return array{
     *     is_valid: bool,
     *     feature_count: int,
     *     valid_count: int,
     *     invalid_count: int,
     *     valid_features: array<int, array<string, mixed>>,
     *     invalid_features: array<int, array{feature: mixed, errors: array<int, string>}>,
     *     errors: array<int, string>
     * }
     */
    public function validate(array|string $input): array
    {
        $errors = [];
        $data = $input;

        if (is_string($input)) {
            $decoded = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'is_valid' => false,
                    'feature_count' => 0,
                    'valid_count' => 0,
                    'invalid_count' => 0,
                    'valid_features' => [],
                    'invalid_features' => [],
                    'errors' => ['JSON format tidak valid: '.json_last_error_msg()],
                ];
            }
            $data = $decoded;
        }

        if (! is_array($data) || empty($data['type'])) {
            return [
                'is_valid' => false,
                'feature_count' => 0,
                'valid_count' => 0,
                'invalid_count' => 0,
                'valid_features' => [],
                'invalid_features' => [],
                'errors' => ['Format GeoJSON tidak valid: Atribut "type" tidak ditemukan.'],
            ];
        }

        $type = $data['type'];
        $rawFeatures = [];

        if ($type === 'FeatureCollection') {
            if (! isset($data['features']) || ! is_array($data['features'])) {
                return [
                    'is_valid' => false,
                    'feature_count' => 0,
                    'valid_count' => 0,
                    'invalid_count' => 0,
                    'valid_features' => [],
                    'invalid_features' => [],
                    'errors' => ['FeatureCollection harus memiliki array "features".'],
                ];
            }
            $rawFeatures = $data['features'];
        } elseif ($type === 'Feature') {
            $rawFeatures = [$data];
        } elseif (in_array($type, ['Polygon', 'MultiPolygon'])) {
            // Direct Geometry object
            $rawFeatures = [
                [
                    'type' => 'Feature',
                    'properties' => [],
                    'geometry' => $data,
                ],
            ];
        } else {
            return [
                'is_valid' => false,
                'feature_count' => 0,
                'valid_count' => 0,
                'invalid_count' => 0,
                'valid_features' => [],
                'invalid_features' => [],
                'errors' => ["Tipe GeoJSON '{$type}' tidak didukung. Hanya mendukung FeatureCollection, Feature, Polygon, atau MultiPolygon."],
            ];
        }

        $validFeatures = [];
        $invalidFeatures = [];

        foreach ($rawFeatures as $index => $feature) {
            $featureErrors = $this->validateFeature($feature);
            if (empty($featureErrors)) {
                $validFeatures[] = $feature;
            } else {
                $invalidFeatures[] = [
                    'feature' => $feature,
                    'errors' => $featureErrors,
                ];
                $errors[] = "Feature #{$index}: ".implode('; ', $featureErrors);
            }
        }

        $total = count($rawFeatures);
        $validCount = count($validFeatures);
        $invalidCount = count($invalidFeatures);

        return [
            'is_valid' => $total > 0 && $invalidCount === 0,
            'feature_count' => $total,
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'valid_features' => $validFeatures,
            'invalid_features' => $invalidFeatures,
            'errors' => $errors,
        ];
    }

    /**
     * Validasi individual GeoJSON Feature.
     *
     * @return array<int, string>
     */
    protected function validateFeature(mixed $feature): array
    {
        $errors = [];

        if (! is_array($feature)) {
            return ['Item feature bukan sebuah objek JSON valid.'];
        }

        if (($feature['type'] ?? '') !== 'Feature') {
            $errors[] = 'Feature harus memiliki type "Feature".';
        }

        if (! isset($feature['geometry']) || ! is_array($feature['geometry'])) {
            $errors[] = 'Feature harus memiliki objek "geometry".';

            return $errors;
        }

        $geometry = $feature['geometry'];
        $geomType = $geometry['type'] ?? '';

        if (! in_array($geomType, ['Polygon', 'MultiPolygon'])) {
            $errors[] = "Geometri harus bertipe Polygon atau MultiPolygon, ditemukan: '{$geomType}'.";

            return $errors;
        }

        if (! isset($geometry['coordinates']) || ! is_array($geometry['coordinates']) || empty($geometry['coordinates'])) {
            $errors[] = 'Array "coordinates" geometri tidak boleh kosong.';

            return $errors;
        }

        $coordinates = $geometry['coordinates'];

        if ($geomType === 'Polygon') {
            $polygonErrors = $this->validatePolygonCoordinates($coordinates);
            $errors = array_merge($errors, $polygonErrors);
        } elseif ($geomType === 'MultiPolygon') {
            $multiErrors = $this->validateMultiPolygonCoordinates($coordinates);
            $errors = array_merge($errors, $multiErrors);
        }

        return $errors;
    }

    /**
     * Validasi struktur koordinat Polygon (Array of Linear Rings).
     *
     * @param  array<mixed>  $rings
     * @return array<int, string>
     */
    protected function validatePolygonCoordinates(array $rings): array
    {
        $errors = [];

        if (empty($rings)) {
            return ['Polygon harus memiliki minimal satu linear ring.'];
        }

        foreach ($rings as $ringIndex => $ring) {
            if (! is_array($ring) || count($ring) < 4) {
                $errors[] = "Linear ring #{$ringIndex} harus memiliki minimal 4 posisi koordinat (3 titik sudut + 1 penutup).";

                continue;
            }

            // Validasi setiap pasangan koordinat [lng, lat]
            foreach ($ring as $ptIndex => $pt) {
                $ptError = $this->validatePoint($pt);
                if ($ptError) {
                    $errors[] = "Ring #{$ringIndex}, Titik #{$ptIndex}: {$ptError}";
                }
            }

            // Validasi ring tertutup (first == last)
            $first = $ring[0];
            $last = $ring[count($ring) - 1];
            if (is_array($first) && is_array($last) && count($first) >= 2 && count($last) >= 2) {
                if (abs($first[0] - $last[0]) > 1e-7 || abs($first[1] - $last[1]) > 1e-7) {
                    $errors[] = "Linear ring #{$ringIndex} tidak tertutup sempurna: koordinat awal [{$first[0]}, {$first[1]}] tidak sama dengan koordinat akhir [{$last[0]}, {$last[1]}].";
                }
            }
        }

        return $errors;
    }

    /**
     * Validasi struktur koordinat MultiPolygon (Array of Polygons).
     *
     * @param  array<mixed>  $polygons
     * @return array<int, string>
     */
    protected function validateMultiPolygonCoordinates(array $polygons): array
    {
        $errors = [];

        if (empty($polygons)) {
            return ['MultiPolygon harus memiliki minimal satu elemen Polygon.'];
        }

        foreach ($polygons as $polyIndex => $polyRings) {
            if (! is_array($polyRings)) {
                $errors[] = "Elemen Polygon #{$polyIndex} pada MultiPolygon tidak valid.";

                continue;
            }

            $polyErrors = $this->validatePolygonCoordinates($polyRings);
            foreach ($polyErrors as $err) {
                $errors[] = "Polygon #{$polyIndex}: {$err}";
            }
        }

        return $errors;
    }

    /**
     * Validasi titik koordinat individual [longitude, latitude] sesuai WGS 84.
     */
    protected function validatePoint(mixed $point): ?string
    {
        if (! is_array($point) || count($point) < 2) {
            return 'Format titik koordinat harus berupa array [longitude, latitude].';
        }

        $lng = $point[0];
        $lat = $point[1];

        if (! is_numeric($lng) || ! is_numeric($lat)) {
            return 'Nilai bujur (longitude) dan lintang (latitude) harus bertipe numerik.';
        }

        $lng = (float) $lng;
        $lat = (float) $lat;

        if ($lng < -180.0 || $lng > 180.0) {
            return "Nilai bujur (longitude) {$lng} berada di luar rentang valid WGS84 [-180, 180].";
        }

        if ($lat < -90.0 || $lat > 90.0) {
            return "Nilai lintang (latitude) {$lat} berada di luar rentang valid WGS84 [-90, 90].";
        }

        return null;
    }
}
