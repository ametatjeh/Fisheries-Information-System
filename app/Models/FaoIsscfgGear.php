<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaoIsscfgGear extends Model
{
    use HasFactory;

    protected $table = 'fao_isscfg_gears';

    protected $fillable = [
        'isscfg_code',
        'standard_abbreviation',
        'name_en',
        'name_id',
        'parent_id',
        'level',
        'description',
        'fao_version',
        'is_active',
        'import_batch_id',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
        'parent_id' => 'integer',
        'import_batch_id' => 'integer',
    ];

    /**
     * Hierarchy parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(FaoIsscfgGear::class, 'parent_id');
    }

    /**
     * Hierarchy children
     */
    public function children(): HasMany
    {
        return $this->hasMany(FaoIsscfgGear::class, 'parent_id');
    }

    /**
     * Local fishing gears mapped to this FAO reference
     */
    public function fishingGears(): HasMany
    {
        return $this->hasMany(FishingGear::class, 'fao_isscfg_gear_id');
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
