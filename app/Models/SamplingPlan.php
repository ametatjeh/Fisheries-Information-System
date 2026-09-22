<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SamplingPlan extends Model
{
    use HasFactory;

    protected $table = 'sampling_plans';

    protected $fillable = [
        'code',
        'title',
        'landing_site_id',
        'target_species_id',
        'start_date',
        'end_date',
        'target_sample_size',
        'sampling_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_sample_size' => 'integer',
    ];

    public function landingSite(): BelongsTo
    {
        return $this->belongsTo(LandingSite::class);
    }

    public function targetSpecies(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'target_species_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }
}
