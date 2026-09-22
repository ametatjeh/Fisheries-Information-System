<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingSite extends Model
{
    use HasFactory;

    protected $table = 'landing_sites';

    protected $fillable = [
        'code',
        'name',
        'site_type',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'address',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'homeport_site_id');
    }

    public function departureTrips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'departure_site_id');
    }

    public function returnTrips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'landing_site_id');
    }

    public function landings(): HasMany
    {
        return $this->hasMany(Landing::class, 'landing_site_id');
    }

    public function samplingPlans(): HasMany
    {
        return $this->hasMany(SamplingPlan::class, 'landing_site_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class, 'landing_site_id');
    }
}
