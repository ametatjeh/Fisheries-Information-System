<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VesselType extends Model
{
    use HasFactory;

    protected $table = 'vessel_types';

    protected $fillable = [
        'code',
        'name',
        'category',
        'tonnage_range',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke armada kapal penangkap ikan dengan tipe ini
     */
    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class);
    }
}
