<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiologicalMeasurement extends Model
{
    use HasFactory;

    protected $table = 'biological_measurements';

    protected $fillable = [
        'sample_id',
        'fish_species_id',
        'specimen_number',
        'fork_length_cm',
        'total_length_cm',
        'standard_length_cm',
        'weight_gram',
        'sex',
        'gonad_maturity_stage',
        'stomach_fullness',
        'notes',
    ];

    protected $casts = [
        'specimen_number' => 'integer',
        'fork_length_cm' => 'decimal:2',
        'total_length_cm' => 'decimal:2',
        'standard_length_cm' => 'decimal:2',
        'weight_gram' => 'decimal:2',
        'gonad_maturity_stage' => 'integer',
        'stomach_fullness' => 'integer',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    public function fishSpecies(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'fish_species_id');
    }

    /**
     * Alias standar untuk relasi jenis ikan (ASFIS Species)
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'fish_species_id');
    }
}
