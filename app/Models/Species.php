<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Species extends Model
{
    protected $table = 'species';

    protected $fillable = [
        'fao_code',
        'taxonomic_code',
        'isscaap_code',
        'functional_group',
        'local_name_id',
        'local_name_variants',
        'indonesia_code',
        'is_indonesia',
        'local_name_aceh',
        'aceh_code',
        'is_aceh',
        'scientific_name',
        'author',
        'english_name',
        'french_name',
        'spanish_name',
        'taxon_level',
        'is_statistical_item',
        'fao_version',
        'fao_source',
        'family',
        'higher_taxa',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_statistical_item' => 'boolean',
        'is_indonesia' => 'boolean',
        'is_aceh' => 'boolean',
        'is_active' => 'boolean',
    ];

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAceh($query)
    {
        return $query->where('is_aceh', true);
    }

    public function scopeIndonesia($query)
    {
        return $query->where('is_indonesia', true);
    }

    public function scopeStatistical($query)
    {
        return $query->where('is_statistical_item', true);
    }

    // RELATIONS
    // Siapkan relasi dummy/struktur. Jangan membuat model transaksi baru.
    // Jika Catches dll sudah ada nantinya, maka relasi ini akan digunakan.

    public function catches()
    {
        return $this->hasMany('App\Models\FishCatch', 'fish_species_id', 'id');
    }

    /**
     * Accessor untuk nama lokal Indonesia (mendukung fallback alias indonesian_name)
     */
    public function getIndonesianNameAttribute(): ?string
    {
        return $this->local_name_id ?? $this->english_name ?? $this->scientific_name;
    }
}
