<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Landing extends Model
{
    use HasFactory;

    protected $table = 'landings';

    protected $fillable = [
        'landing_number',
        'fishing_trip_id',
        'landing_site_id',
        'landing_date',
        'recorded_by',
        'total_weight_kg',
        'total_value_rp',
        'buyer_count',
        'notes',
    ];

    protected $casts = [
        'landing_date' => 'datetime',
        'total_weight_kg' => 'decimal:2',
        'total_value_rp' => 'decimal:2',
        'buyer_count' => 'integer',
    ];

    /**
     * Relasi ke trip asal pendaratan
     */
    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke pangkalan / TPI pendaratan
     */
    public function landingSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class, 'landing_site_id');
    }

    /**
     * Relasi ke petugas pencatat pendaratan
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Relasi ke rincian komoditas ikan yang didaratkan
     */
    public function items(): HasMany
    {
        return $this->hasMany(LandingItem::class, 'landing_id');
    }
}
