<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class FisherGroup extends Model
{
    use HasFactory;

    protected $table = 'fisher_groups';

    protected $fillable = [
        'code',
        'name',
        'regency_id',
        'district_id',
        'village_id',
        'leader_name',
        'phone',
        'address',
        'established_date',
        'total_members',
    ];

    protected $casts = [
        'established_date' => 'date',
        'total_members' => 'integer',
    ];

    /**
     * Relasi ke Kabupaten / Kota
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * Relasi ke Kecamatan
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Relasi ke Desa / Gampong
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Relasi ke anggota nelayan dalam kelompok
     */
    public function members(): HasMany
    {
        return $this->hasMany(Fisherman::class, 'fisher_group_id');
    }

    /**
     * Relasi ke seluruh kapal milik anggota kelompok ini
     */
    public function vessels(): HasManyThrough
    {
        return $this->hasManyThrough(Vessel::class, Fisherman::class, 'fisher_group_id', 'owner_id');
    }
}
