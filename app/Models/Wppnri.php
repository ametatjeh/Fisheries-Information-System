<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wppnri extends Model
{
    use HasFactory;

    protected $table = 'wppnri';

    protected $fillable = [
        'fao_fishing_area_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fao_fishing_area_id' => 'integer',
    ];

    public function faoFishingArea(): BelongsTo
    {
        return $this->belongsTo(FaoFishingArea::class, 'fao_fishing_area_id');
    }

    public function fishingGrounds(): HasMany
    {
        return $this->hasMany(FishingGround::class, 'wppnri_id');
    }

    public function fishingTrips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'wppnri_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
