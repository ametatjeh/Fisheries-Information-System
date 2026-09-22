<?php

namespace App\Models\Gfw;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GfwVessel extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'gfw';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gfw_vessels';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gfw_vessel_id',
        'name',
        'ship_name',
        'mmsi',
        'imo',
        'callsign',
        'flag',
        'vessel_type',
        'vessel_class',
        'gear_type',
        'length_m',
        'tonnage_gt',
        'gross_tonnage',
        'engine_power_kw',
        'source',
        'source_version',
        'first_seen_at',
        'last_seen_at',
        'last_updated_at',
        'last_synced_at',
        'raw_hash',
        'raw_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_updated_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'length_m' => 'float',
            'tonnage_gt' => 'float',
            'engine_power_kw' => 'float',
            'raw_data' => 'array',
        ];
    }

    /**
     * Relationship to GfwVesselPresence records.
     */
    public function presences(): HasMany
    {
        return $this->hasMany(GfwVesselPresence::class, 'gfw_vessel_id', 'gfw_vessel_id');
    }
}
