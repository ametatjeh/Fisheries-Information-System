<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GfwVesselActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gfw_vessel_activities';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gfw_vessel_id',
        'activity_type',
        'region_key',
        'latitude',
        'longitude',
        'observation_timestamp',
        'period_start',
        'period_end',
        'hours',
        'distance_km',
        'speed_knots',
        'raw_data',
        'last_synced_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'hours' => 'float',
            'distance_km' => 'float',
            'speed_knots' => 'float',
            'observation_timestamp' => 'datetime',
            'period_start' => 'date',
            'period_end' => 'date',
            'last_synced_at' => 'datetime',
            'raw_data' => 'array',
        ];
    }

    /**
     * Associated GFW vessel profile if already synchronized.
     */
    public function gfwVessel(): BelongsTo
    {
        return $this->belongsTo(GfwVessel::class, 'gfw_vessel_id', 'gfw_vessel_id');
    }
}
