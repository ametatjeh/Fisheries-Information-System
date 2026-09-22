<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vessel extends Model
{
    use HasFactory;

    protected $table = 'vessels';

    protected $fillable = [
        'name',
        'registration_number',
        'owner_id',
        'vessel_type',
        'vessel_type_id',
        'gross_tonnage',
        'length',
        'width',
        'depth',
        'engine_power_hp',
        'engine_brand',
        'build_year',
        'homeport_site_id',
        'primary_gear_id',
        'is_active',
    ];

    protected $casts = [
        'gross_tonnage' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'depth' => 'decimal:2',
        'engine_power_hp' => 'decimal:2',
        'build_year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke nelayan pemilik kapal
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Fisherman::class, 'owner_id');
    }

    /**
     * Relasi ke master tipe armada kapal
     */
    public function vesselType(): BelongsTo
    {
        return $this->belongsTo(VesselType::class, 'vessel_type_id');
    }

    /**
     * Relasi ke master alat tangkap utama
     */
    public function primaryGear(): BelongsTo
    {
        return $this->belongsTo(FishingGear::class, 'primary_gear_id');
    }

    /**
     * Relasi ke pangkalan / homeport
     */
    public function homeportSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class, 'homeport_site_id');
    }

    /**
     * Relasi ke riwayat trip penangkapan kapal ini
     */
    public function fishingTrips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'vessel_id');
    }
}
