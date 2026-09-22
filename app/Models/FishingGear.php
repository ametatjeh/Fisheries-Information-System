<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingGear extends Model
{
    use HasFactory;

    protected $table = 'fishing_gears';

    protected $fillable = [
        'parent_id',
        'code',
        'isscfg_code',
        'standard_abbreviation',
        'name',
        'name_en',
        'name_id',
        'local_name',
        'category',
        'level',
        'source',
        'description',
        'is_active',
        'sort_order',
        'fao_isscfg_gear_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Relasi ke kategori induk (hierarki lokal)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FishingGear::class, 'parent_id');
    }

    /**
     * Relasi ke subkategori / jenis turunan
     */
    public function children(): HasMany
    {
        return $this->hasMany(FishingGear::class, 'parent_id')->orderBy('sort_order')->orderBy('code');
    }

    /**
     * Relasi ke reference FAO ISSCFG
     */
    public function faoIsscfgGear(): BelongsTo
    {
        return $this->belongsTo(FaoIsscfgGear::class, 'fao_isscfg_gear_id');
    }

    /**
     * Relasi ke kapal yang menggunakan alat tangkap ini sebagai alat utama
     */
    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'primary_gear_id');
    }

    /**
     * Relasi ke trip penangkapan yang menggunakan alat ini sebagai alat utama
     */
    public function fishingTrips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'primary_gear_id');
    }

    /**
     * Relasi ke upaya penangkapan (fishing efforts) yang menggunakan alat ini
     */
    public function fishingEfforts(): HasMany
    {
        return $this->hasMany(FishingEffort::class, 'fishing_gear_id');
    }

    /**
     * Scope data aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope data standar FAO/CWP ISSCFG
     */
    public function scopeStandardFao(Builder $query): Builder
    {
        return $query->where('source', 'FAO_ISSCFG');
    }

    /**
     * Scope data operasional lokal
     */
    public function scopeLocal(Builder $query): Builder
    {
        return $query->where('source', 'LOCAL');
    }
}
