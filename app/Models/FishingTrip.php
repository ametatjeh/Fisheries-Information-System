<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingTrip extends Model
{
    use HasFactory;

    protected $table = 'fishing_trips';

    protected $fillable = [
        'trip_number',
        'vessel_id',
        'captain_id',
        'departure_site_id',
        'landing_site_id',
        'departure_date',
        'return_date',
        'crew_count',
        'fuel_consumption_liters',
        'ice_consumption_kg',
        'primary_gear_id',
        'wppnri_id',
        'fishing_ground_id',
        'fishing_ground_name',
        'fma_code',
        'validation_status',
        'submitted_by',
        'submitted_at',
        'validated_by',
        'validated_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'return_date' => 'datetime',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
        'crew_count' => 'integer',
        'fuel_consumption_liters' => 'decimal:2',
        'ice_consumption_kg' => 'decimal:2',
    ];

    /**
     * Relasi ke kapal operasi
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    /**
     * Relasi ke nahkoda kapal
     */
    public function captain(): BelongsTo
    {
        return $this->belongsTo(Fisherman::class, 'captain_id');
    }

    /**
     * Relasi ke pelabuhan/TPI keberangkatan
     */
    public function departureSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class, 'departure_site_id');
    }

    /**
     * Relasi ke pelabuhan/TPI pendaratan
     */
    public function landingSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class, 'landing_site_id');
    }

    /**
     * Relasi ke alat tangkap utama trip
     */
    public function primaryGear(): BelongsTo
    {
        return $this->belongsTo(FishingGear::class, 'primary_gear_id');
    }

    /**
     * Relasi ke enumerator yang menginput
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relasi ke validator data
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relasi ke rincian hasil tangkapan ikan pada trip ini
     */
    public function catches(): HasMany
    {
        return $this->hasMany(FishCatch::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke catatan pendaratan ikan dari trip ini
     */
    public function landings(): HasMany
    {
        return $this->hasMany(Landing::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke buku catatan harian (logbooks) kapal dalam trip ini
     */
    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke upaya penangkapan (fishing efforts / setting & hauling) dalam trip ini
     */
    public function fishingEfforts(): HasMany
    {
        return $this->hasMany(FishingEffort::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke riwayat log audit validasi trip
     */
    public function validationLogs(): HasMany
    {
        return $this->hasMany(ValidationLog::class, 'fishing_trip_id')->orderByDesc('created_at');
    }

    /**
     * Relasi ke batch pengambilan sampel biologis dari trip ini
     */
    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class, 'fishing_trip_id');
    }

    public function wppnri(): BelongsTo
    {
        return $this->belongsTo(Wppnri::class, 'wppnri_id');
    }

    public function fishingGround(): BelongsTo
    {
        return $this->belongsTo(FishingGround::class, 'fishing_ground_id');
    }
}
