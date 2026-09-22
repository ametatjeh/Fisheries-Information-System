<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FishCatch extends Model
{
    use HasFactory;

    protected $table = 'catches';

    protected $fillable = [
        'fishing_trip_id',
        'fishing_effort_id',
        'fish_species_id',
        'weight_kg',
        'fish_count',
        'catch_status',
        'notes',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'fish_count' => 'integer',
    ];

    /**
     * Relasi ke trip penangkapan
     */
    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }

    /**
     * Alias singkat untuk relasi trip
     */
    public function trip(): BelongsTo
    {
        return $this->fishingTrip();
    }

    /**
     * Relasi ke master jenis ikan
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'fish_species_id');
    }

    /**
     * Relasi ke siklus upaya penurunan alat (fishing effort)
     */
    public function fishingEffort(): BelongsTo
    {
        return $this->belongsTo(FishingEffort::class, 'fishing_effort_id');
    }
}
