<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'regency_id',
        'code',
        'name',
    ];

    /**
     * Relasi ke kabupaten/kota
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    /**
     * Relasi ke desa/kelurahan
     */
    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }
}
