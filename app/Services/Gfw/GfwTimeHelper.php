<?php

namespace App\Services\Gfw;

use Carbon\Carbon;
use DateTimeInterface;
use Throwable;

class GfwTimeHelper
{
    /**
     * Convert any timestamp or DateTimeInterface into an explicit Zulu (UTC) ISO-8601 string.
     * Example output: '2026-09-22T16:53:00Z'.
     */
    public static function toExplicitZuluString(DateTimeInterface|string|null $timestamp): ?string
    {
        if ($timestamp === null || $timestamp === '') {
            return null;
        }

        try {
            return Carbon::parse($timestamp, 'UTC')->utc()->toIso8601ZuluString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Calculate data age in seconds between a given timestamp and the reference time (defaults to now in UTC).
     */
    public static function calculateAgeSeconds(DateTimeInterface|string|null $timestamp, ?Carbon $now = null): ?int
    {
        if ($timestamp === null || $timestamp === '') {
            return null;
        }

        try {
            $parsed = Carbon::parse($timestamp, 'UTC')->utc();
            $reference = $now ? $now->copy()->utc() : Carbon::now('UTC');

            return max(0, (int) ($reference->getTimestamp() - $parsed->getTimestamp()));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Format elapsed seconds into Indonesian human-readable relative time.
     *
     * Rules:
     * - < 60 detik: "X detik yang lalu"
     * - < 60 menit (3600 detik): "X menit yang lalu"
     * - < 24 jam (86400 detik): "X jam yang lalu"
     * - >= 24 jam: "X hari Y jam yang lalu" (or "X hari yang lalu" if remaining hours is 0)
     */
    public static function formatAge(?int $seconds): string
    {
        if ($seconds === null || $seconds < 0) {
            return '-';
        }

        if ($seconds < 60) {
            return "{$seconds} detik yang lalu";
        }

        if ($seconds < 3600) {
            $minutes = intdiv($seconds, 60);

            return "{$minutes} menit yang lalu";
        }

        if ($seconds < 86400) {
            $hours = intdiv($seconds, 3600);

            return "{$hours} jam yang lalu";
        }

        $days = intdiv($seconds, 86400);
        $remainingHours = intdiv($seconds % 86400, 3600);

        if ($remainingHours > 0) {
            return "{$days} hari {$remainingHours} jam yang lalu";
        }

        return "{$days} hari yang lalu";
    }

    /**
     * Format a timestamp directly into Indonesian relative time.
     */
    public static function formatRelativeTime(DateTimeInterface|string|null $timestamp, ?Carbon $now = null): string
    {
        $ageSeconds = self::calculateAgeSeconds($timestamp, $now);

        return self::formatAge($ageSeconds);
    }
}
