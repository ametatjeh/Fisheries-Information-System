<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingGround extends Model
{
    use HasFactory;

    protected $table = 'fishing_grounds';

    protected $fillable = [
        'wppnri_id',
        'code',
        'name',
        'description',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'wppnri_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function wppnri(): BelongsTo
    {
        return $this->belongsTo(Wppnri::class, 'wppnri_id');
    }

    public function fishingTrips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'fishing_ground_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
