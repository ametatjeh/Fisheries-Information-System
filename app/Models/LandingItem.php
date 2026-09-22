<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingItem extends Model
{
    use HasFactory;

    protected $table = 'landing_items';

    protected $fillable = [
        'landing_id',
        'fish_species_id',
        'weight_kg',
        'fish_count',
        'price_per_kg',
        'total_price',
        'quality_grade',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'fish_count' => 'integer',
        'price_per_kg' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function landing(): BelongsTo
    {
        return $this->belongsTo(Landing::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class, 'fish_species_id');
    }
}
