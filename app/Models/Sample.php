<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sample extends Model
{
    use HasFactory;

    protected $table = 'samples';

    protected $fillable = [
        'sample_code',
        'sampling_plan_id',
        'fishing_trip_id',
        'landing_site_id',
        'enumerator_id',
        'sample_date',
        'total_specimens',
        'total_weight_kg',
        'notes',
    ];

    protected $casts = [
        'sample_date' => 'date',
        'total_specimens' => 'integer',
        'total_weight_kg' => 'decimal:2',
    ];

    public function samplingPlan(): BelongsTo
    {
        return $this->belongsTo(SamplingPlan::class);
    }

    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class);
    }

    public function landingSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class);
    }

    public function enumerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enumerator_id');
    }

    public function biologicalMeasurements(): HasMany
    {
        return $this->hasMany(BiologicalMeasurement::class);
    }
}
