<?php

namespace App\Services\Rzwp3k;

use Illuminate\Support\Facades\Http;
use Throwable;

class Rzwp3kSourceHealthCheck
{
    /**
     * Audit all registered sources in config/rzwp3k.php.
     *
     * @return array<string, array<string, mixed>>
     */
    public function auditAll(): array
    {
        $sources = config('rzwp3k.sources', []);
        $results = [];

        foreach ($sources as $key => $config) {
            $results[$key] = $this->checkSource($key, $config);
        }

        return $results;
    }

    /**
     * Audit a single source entry.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function checkSource(string $key, array $config): array
    {
        $url = $config['dataset_url'] ?? $config['service_url'] ?? null;
        $isVerified = (bool) ($config['verified'] ?? false);
        $sourceType = $config['source_type'] ?? 'unknown';

        $result = [
            'key' => $key,
            'name' => $config['name'] ?? 'Unnamed Source',
            'authority' => $config['authority'] ?? 'Unknown Authority',
            'legal_basis' => $config['legal_basis'] ?? 'N/A',
            'source_type' => $sourceType,
            'url' => $url,
            'crs' => $config['crs'] ?? 'Unknown',
            'layer_name' => $config['layer_name'] ?? null,
            'format' => $config['format'] ?? 'Unknown',
            'is_verified' => $isVerified,
            'verified_at' => $config['verified_at'] ?? null,
            'reachable' => false,
            'http_status' => null,
            'status_label' => 'PENDING_ATTACHMENT',
            'notes' => $config['verification_notes'] ?? '',
        ];

        if (empty($url)) {
            $result['status_label'] = 'NO_ENDPOINT_ATTACHED';
            $result['notes'] = $result['notes'] ?: 'No active direct URL configured. Geometry remains unattached (safe default).';

            return $result;
        }

        // Only perform live HTTP check if verified or explicit URL provided
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'SistemPerikananAceh/1.0'])
                ->get($url);

            $result['http_status'] = $response->status();
            $result['reachable'] = $response->successful();
            $result['status_label'] = $response->successful() ? 'ONLINE' : 'HTTP_'.$response->status();
        } catch (Throwable $e) {
            $result['reachable'] = false;
            $result['status_label'] = 'UNREACHABLE';
            $result['notes'] .= ' (Connection error: '.$e->getMessage().')';
        }

        return $result;
    }
}
