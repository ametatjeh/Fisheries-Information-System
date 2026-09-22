<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingEffort extends Model
{
    use HasFactory;

    protected $table = 'fishing_efforts';

    protected $fillable = [
        'fishing_trip_id',
        'fishing_gear_id',
        'setting_number',
        'setting_date',
        'hauling_date',
        'duration_hours',
        'setting_count',
        'hook_count',
        'net_length_meters',
        'latitude_setting',
        'longitude_setting',
        'latitude_hauling',
        'longitude_hauling',
    ];

    protected $casts = [
        'setting_date' => 'datetime',
        'hauling_date' => 'datetime',
        'duration_hours' => 'decimal:2',
        'net_length_meters' => 'decimal:2',
        'latitude_setting' => 'decimal:7',
        'longitude_setting' => 'decimal:7',
        'latitude_hauling' => 'decimal:7',
        'longitude_hauling' => 'decimal:7',
        'setting_number' => 'integer',
        'setting_count' => 'integer',
        'hook_count' => 'integer',
    ];

    /**
     * Relasi ke trip penangkapan ikan
     */
    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke alat penangkapan ikan yang digunakan
     */
    public function fishingGear(): BelongsTo
    {
        return $this->belongsTo(FishingGear::class, 'fishing_gear_id');
    }

    /**
     * Relasi ke hasil tangkapan yang terkait dengan siklus setting ini
     */
    public function catches(): HasMany
    {
        return $this->hasMany(FishCatch::class, 'fishing_effort_id');
    }
}
