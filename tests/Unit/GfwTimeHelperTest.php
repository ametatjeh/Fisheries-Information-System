<?php

namespace Tests\Unit;

use App\Services\Gfw\GfwTimeHelper;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class GfwTimeHelperTest extends TestCase
{
    /**
     * Test input 2026-09-22T16:53:00Z produces explicit Zulu ISO-8601 string.
     */
    public function test_to_explicit_zulu_string_formats_correctly(): void
    {
        $input = '2026-09-22T16:53:00Z';
        $zulu = GfwTimeHelper::toExplicitZuluString($input);

        $this->assertEquals('2026-09-22T16:53:00Z', $zulu);

        // Also test database format (without Z)
        $dbDate = '2026-09-22 16:53:15';
        $dbZulu = GfwTimeHelper::toExplicitZuluString($dbDate);
        $this->assertEquals('2026-09-22T16:53:15Z', $dbZulu);
    }

    /**
     * Test that an old timestamp is never 0 seconds, and changes according to actual reference time.
     */
    public function test_old_timestamp_is_not_zero_seconds_and_changes_with_actual_time(): void
    {
        $timestamp = '2026-09-22T16:53:00Z';

        // 10 seconds later
        $time1 = Carbon::parse('2026-09-22T16:53:10Z');
        $age1 = GfwTimeHelper::calculateAgeSeconds($timestamp, $time1);
        $this->assertEquals(10, $age1);
        $this->assertEquals('10 detik yang lalu', GfwTimeHelper::formatRelativeTime($timestamp, $time1));

        // 1 hour later
        $time2 = Carbon::parse('2026-09-22T17:53:00Z');
        $age2 = GfwTimeHelper::calculateAgeSeconds($timestamp, $time2);
        $this->assertNotEquals(0, $age2);
        $this->assertEquals(3600, $age2);
        $this->assertEquals('1 jam yang lalu', GfwTimeHelper::formatRelativeTime($timestamp, $time2));

        // Difference between the two times proves age dynamically changes with actual time
        $this->assertGreaterThan($age1, $age2);

        // Simulated current time: 25 Sep 2026 01:13:48 UTC
        $currentTime = Carbon::parse('2026-09-25T01:13:48Z');
        $currentAge = GfwTimeHelper::calculateAgeSeconds($timestamp, $currentTime);
        $this->assertNotEquals(0, $currentAge);
        $this->assertGreaterThan(100000, $currentAge);
    }

    /**
     * Test relative time formatting under 60 seconds: "X detik yang lalu".
     */
    public function test_format_less_than_60_seconds(): void
    {
        $this->assertEquals('0 detik yang lalu', GfwTimeHelper::formatAge(0));
        $this->assertEquals('15 detik yang lalu', GfwTimeHelper::formatAge(15));
        $this->assertEquals('59 detik yang lalu', GfwTimeHelper::formatAge(59));

        $timestamp = '2026-09-22T16:53:00Z';
        $now = Carbon::parse('2026-09-22T16:53:45Z');
        $this->assertEquals('45 detik yang lalu', GfwTimeHelper::formatRelativeTime($timestamp, $now));
    }

    /**
     * Test relative time formatting under 60 minutes: "X menit yang lalu".
     */
    public function test_format_less_than_60_minutes(): void
    {
        $this->assertEquals('1 menit yang lalu', GfwTimeHelper::formatAge(60));
        $this->assertEquals('25 menit yang lalu', GfwTimeHelper::formatAge(1500));
        $this->assertEquals('59 menit yang lalu', GfwTimeHelper::formatAge(3599));

        $timestamp = '2026-09-22T16:53:00Z';
        $now = Carbon::parse('2026-09-22T17:18:00Z'); // 25 minutes later
        $this->assertEquals('25 menit yang lalu', GfwTimeHelper::formatRelativeTime($timestamp, $now));
    }

    /**
     * Test relative time formatting under 24 hours: "X jam yang lalu".
     */
    public function test_format_less_than_24_hours(): void
    {
        $this->assertEquals('1 jam yang lalu', GfwTimeHelper::formatAge(3600));
        $this->assertEquals('5 jam yang lalu', GfwTimeHelper::formatAge(18000));
        $this->assertEquals('23 jam yang lalu', GfwTimeHelper::formatAge(86399));

        $timestamp = '2026-09-22T16:53:00Z';
        $now = Carbon::parse('2026-09-23T04:53:00Z'); // 12 hours later
        $this->assertEquals('12 jam yang lalu', GfwTimeHelper::formatRelativeTime($timestamp, $now));
    }

    /**
     * Test relative time formatting over 24 hours: "2 hari 15 jam yang lalu".
     */
    public function test_format_more_than_24_hours_formats_days_and_hours(): void
    {
        // Exactly 2 days and 15 hours = (2 * 86400) + (15 * 3600) = 172800 + 54000 = 226800 seconds
        $seconds = (2 * 86400) + (15 * 3600);
        $this->assertEquals('2 hari 15 jam yang lalu', GfwTimeHelper::formatAge($seconds));

        // Exactly 2 days and 15 hours from 2026-09-22T16:53:00Z
        $timestamp = '2026-09-22T16:53:00Z';
        $now = Carbon::parse('2026-09-25T07:53:00Z'); // 2 days and 15 hours later
        $this->assertEquals('2 hari 15 jam yang lalu', GfwTimeHelper::formatRelativeTime($timestamp, $now));

        // Multiple days without remaining hours
        $exactDays = 3 * 86400;
        $this->assertEquals('3 hari yang lalu', GfwTimeHelper::formatAge($exactDays));
    }
}
