<?php

namespace App\Models\Gfw;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GfwVesselPresence extends Model
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
    protected $table = 'gfw_vessel_presence';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gfw_vessel_id',
        'aoi',
        'observed_at',
        'latitude',
        'longitude',
        'speed',
        'course',
        'vessel_type',
        'flag',
        'source_dataset',
        'source_version',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'speed' => 'float',
            'course' => 'float',
        ];
    }

    /**
     * Internal relationship to GfwVessel.
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(GfwVessel::class, 'gfw_vessel_id', 'gfw_vessel_id');
    }
}
