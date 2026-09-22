<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaoIsscaapGroup extends Model
{
    use HasFactory;

    protected $table = 'fao_isscaap_groups';

    protected $fillable = [
        'isscaap_code',
        'name_en',
        'name_id',
        'description',
        'fao_version',
        'is_active',
        'import_batch_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'import_batch_id' => 'integer',
    ];

    /**
     * ASFIS species belonging to this ISSCAAP group
     */
    public function faoAsfisSpecies(): HasMany
    {
        return $this->hasMany(FaoAsfisSpecies::class, 'isscaap_code', 'isscaap_code');
    }

    /**
     * The import batch this reference belongs to
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ReferenceImport::class, 'import_batch_id');
    }

    /**
     * Scope active records
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
