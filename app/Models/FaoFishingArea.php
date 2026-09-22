<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaoFishingArea extends Model
{
    use HasFactory;

    protected $table = 'fao_fishing_areas';

    protected $fillable = [
        'code',
        'name_en',
        'is_active',
        'import_batch_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'import_batch_id' => 'integer',
    ];

    public function wppnri(): HasMany
    {
        return $this->hasMany(Wppnri::class, 'fao_fishing_area_id');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ReferenceImport::class, 'import_batch_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
