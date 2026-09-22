<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fisherman extends Model
{
    use HasFactory;

    protected $table = 'fishers';

    protected $fillable = [
        'user_id',
        'nik',
        'kusuka_number',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'phone',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'address',
        'fisher_group_id',
        'fisher_type',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke akun user login jika nelayan punya akun sistem
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke kelompok nelayan / KUB
     */
    public function fisherGroup(): BelongsTo
    {
        return $this->belongsTo(FisherGroup::class, 'fisher_group_id');
    }

    /**
     * Relasi wilayah: Provinsi
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Relasi wilayah: Kabupaten / Kota
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * Relasi wilayah: Kecamatan
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Relasi wilayah: Desa / Gampong
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Relasi ke kapal milik nelayan ini
     */
    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'owner_id');
    }

    /**
     * Relasi ke trip penangkapan di mana nelayan bertindak sebagai nahkoda
     */
    public function tripsAsCaptain(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'captain_id');
    }
}
