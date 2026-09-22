<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    use HasFactory;

    protected $table = 'logbooks';

    protected $fillable = [
        'fishing_trip_id',
        'log_date',
        'log_time',
        'latitude',
        'longitude',
        'weather_condition',
        'wave_height_meters',
        'sea_condition',
        'activity_description',
    ];

    protected $casts = [
        'log_date' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'wave_height_meters' => 'decimal:2',
    ];

    /**
     * Relasi ke trip penangkapan ikan induk
     */
    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }
}
