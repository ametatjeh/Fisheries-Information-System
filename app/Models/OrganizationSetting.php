<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OrganizationSetting extends Model
{
    use HasFactory;

    protected $table = 'application_settings';

    protected $fillable = [
        'organization_name',
        'organization_type',
        'logo_path',
        'address',
        'website',
        'email',
        'phone',
    ];

    public const CACHE_KEY = 'application_organization_setting';

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Ambil data identitas organisasi aplikasi dengan caching.
     */
    public static function getSettings(): self
    {
        $setting = Cache::remember(self::CACHE_KEY, now()->addDay(), function () {
            return static::first() ?? static::create([
                'organization_name' => 'Dinas Kelautan dan Perikanan Aceh',
                'organization_type' => 'Instansi',
            ]);
        });

        if (! ($setting instanceof self)) {
            static::clearCache();

            return static::first() ?? static::create([
                'organization_name' => 'Dinas Kelautan dan Perikanan Aceh',
                'organization_type' => 'Instansi',
            ]);
        }

        return $setting;
    }

    /**
     * Bersihkan cache identitas organisasi.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Accessor URL Logo organisasi (fallback ke asset Logo.png jika belum diunggah).
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }

        return asset('Logo.png');
    }
}
