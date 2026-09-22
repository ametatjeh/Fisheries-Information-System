<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaoAsfisSpecies extends Model
{
    protected $table = 'fao_asfis_species';

    protected $fillable = [
        'fao_code',
        'taxonomic_code',
        'isscaap_code',
        'scientific_name',
        'author',
        'english_name',
        'french_name',
        'spanish_name',
        'family',
        'higher_taxa',
        'taxon_level',
        'is_statistical_item',
        'fao_version',
        'fao_source',
        'import_batch_id',
        'is_active',
    ];

    protected $casts = [
        'is_statistical_item' => 'boolean',
        'is_active' => 'boolean',
        'import_batch_id' => 'integer',
    ];

    /**
     * ISSCAAP Group
     */
    public function faoIsscaapGroup(): BelongsTo
    {
        return $this->belongsTo(FaoIsscaapGroup::class, 'isscaap_code', 'isscaap_code');
    }

    /**
     * Scope active records
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
