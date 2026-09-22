<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id',
        'code',
        'name',
        'type',
    ];

    /**
     * Relasi ke provinsi
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Relasi ke kecamatan
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    /**
     * Nama lengkap dengan tipe (Kabupaten X / Kota Y)
     */
    public function getFullNameAttribute(): string
    {
        return ucfirst($this->type).' '.$this->name;
    }
}
