<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GfwEvent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gfw_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gfw_event_id',
        'event_type',
        'gfw_vessel_id',
        'secondary_vessel_id',
        'region_key',
        'latitude',
        'longitude',
        'start_time',
        'end_time',
        'duration_hours',
        'confidence',
        'port_name',
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
            'duration_hours' => 'float',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'last_synced_at' => 'datetime',
            'raw_data' => 'array',
        ];
    }

    /**
     * Associated primary GFW vessel identity.
     */
    public function primaryVessel(): BelongsTo
    {
        return $this->belongsTo(GfwVessel::class, 'gfw_vessel_id', 'gfw_vessel_id');
    }

    /**
     * Associated secondary GFW vessel identity (for encounters).
     */
    public function secondaryVessel(): BelongsTo
    {
        return $this->belongsTo(GfwVessel::class, 'secondary_vessel_id', 'gfw_vessel_id');
    }
}
