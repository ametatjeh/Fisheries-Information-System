<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify the reCAPTCHA v3 token with Google's siteverify endpoint.
     *
     * @return array{success: bool, error: ?string, score: ?float, action: ?string, hostname: ?string}
     */
    public function verify(?string $token, ?string $clientIp = null): array
    {
        if (empty($token)) {
            return [
                'success' => false,
                'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                'score' => null,
                'action' => null,
                'hostname' => null,
            ];
        }

        $secretKey = (string) config('recaptcha.secret_key', '');
        if ($secretKey === '') {
            Log::warning('reCAPTCHA verification skipped or failed: secret key is not configured.');

            return [
                'success' => false,
                'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                'score' => null,
                'action' => null,
                'hostname' => null,
            ];
        }

        try {
            $verifyUrl = (string) config('recaptcha.verify_url', 'https://www.google.com/recaptcha/api/siteverify');
            $timeout = (int) config('recaptcha.timeout', 10);

            $payload = [
                'secret' => $secretKey,
                'response' => $token,
            ];

            if (! empty($clientIp)) {
                $payload['remoteip'] = $clientIp;
            }

            $response = Http::asForm()
                ->timeout($timeout)
                ->connectTimeout(5)
                ->post($verifyUrl, $payload);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA verification failed: Google API HTTP error status '.$response->status());

                return [
                    'success' => false,
                    'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                    'score' => null,
                    'action' => null,
                    'hostname' => null,
                ];
            }

            $data = $response->json();
            if (! is_array($data)) {
                Log::warning('reCAPTCHA verification failed: invalid response format from Google.');

                return [
                    'success' => false,
                    'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                    'score' => null,
                    'action' => null,
                    'hostname' => null,
                ];
            }

            $isSuccess = (bool) ($data['success'] ?? false);
            $action = isset($data['action']) ? (string) $data['action'] : null;
            $hostname = isset($data['hostname']) ? (string) $data['hostname'] : null;
            $score = isset($data['score']) ? (float) $data['score'] : null;

            if (! $isSuccess) {
                Log::warning('reCAPTCHA verification failed: Google reported success = false.');

                return [
                    'success' => false,
                    'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                    'score' => $score,
                    'action' => $action,
                    'hostname' => $hostname,
                ];
            }

            // 1. Validate Action
            $expectedAction = (string) config('recaptcha.login_action', 'login');
            if ($action !== $expectedAction) {
                Log::warning('reCAPTCHA verification failed: action mismatch.');

                return [
                    'success' => false,
                    'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                    'score' => $score,
                    'action' => $action,
                    'hostname' => $hostname,
                ];
            }

            // 2. Validate Hostname
            $allowedHostnames = (array) config('recaptcha.allowed_hostnames', []);
            if (! empty($allowedHostnames)) {
                $allowedLower = array_map('strtolower', $allowedHostnames);
                if ($hostname === null || ! in_array(strtolower($hostname), $allowedLower, true)) {
                    Log::warning('reCAPTCHA verification failed: hostname mismatch.');

                    return [
                        'success' => false,
                        'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                        'score' => $score,
                        'action' => $action,
                        'hostname' => $hostname,
                    ];
                }
            }

            // 3. Validate Score Threshold
            $expectedScore = (float) config('recaptcha.login_score', 0.5);
            if ($score === null || $score < $expectedScore) {
                Log::warning('reCAPTCHA verification failed: score below threshold.');

                return [
                    'success' => false,
                    'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                    'score' => $score,
                    'action' => $action,
                    'hostname' => $hostname,
                ];
            }

            return [
                'success' => true,
                'error' => null,
                'score' => $score,
                'action' => $action,
                'hostname' => $hostname,
            ];
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification exception caught: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.',
                'score' => null,
                'action' => null,
                'hostname' => null,
            ];
        }
    }
}
