<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatchEstimation extends Model
{
    use HasFactory;

    protected $table = 'catch_estimations';

    protected $fillable = [
        'regency_id',
        'landing_site_id',
        'fish_species_id',
        'fishing_gear_id',
        'year',
        'month',
        'sampled_catch_kg',
        'raising_factor',
        'estimated_catch_kg',
        'estimated_effort_trips',
        'cpue',
        'variance',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'sampled_catch_kg' => 'decimal:2',
        'raising_factor' => 'decimal:4',
        'estimated_catch_kg' => 'decimal:2',
        'estimated_effort_trips' => 'integer',
        'cpue' => 'decimal:4',
        'variance' => 'decimal:4',
    ];

    protected $appends = ['status'];

    public function getStatusAttribute(): string
    {
        if (isset($this->attributes['status'])) {
            return $this->attributes['status'];
        }

        if (preg_match('/\[STATUS:(draft|validated|rejected)\]/', $this->notes ?? '', $matches)) {
            return $matches[1];
        }

        return 'validated'; // Rekor historis default dinyatakan validated
    }

    public function setStatusAttribute(string $value): void
    {
        $cleanNotes = preg_replace('/\[STATUS:(draft|validated|rejected)\]/', '', $this->notes ?? '');
        $cleanNotes = trim($cleanNotes);
        $this->attributes['notes'] = $cleanNotes ? "{$cleanNotes} [STATUS:{$value}]" : "[STATUS:{$value}]";
    }

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
