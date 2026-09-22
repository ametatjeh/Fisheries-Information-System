<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyProductionStatistic extends Model
{
    use HasFactory;

    protected $table = 'monthly_production_statistics';

    protected $fillable = [
        'regency_id',
        'landing_site_id',
        'fish_species_id',
        'fishing_gear_id',
        'year',
        'month',
        'total_volume_kg',
        'total_value_rp',
        'average_price_per_kg',
        'total_active_vessels',
        'total_trips',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_volume_kg' => 'decimal:2',
        'total_value_rp' => 'decimal:2',
        'average_price_per_kg' => 'decimal:2',
        'total_active_vessels' => 'integer',
        'total_trips' => 'integer',
    ];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function landingSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class);
    }

    public function fishSpecies(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'fish_species_id');
    }

    /**
     * Alias standar untuk relasi jenis ikan ASFIS
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'fish_species_id');
    }

    public function fishingGear(): BelongsTo
    {
        return $this->belongsTo(FishingGear::class);
    }
}
