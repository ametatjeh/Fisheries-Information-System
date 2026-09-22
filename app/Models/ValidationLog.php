<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidationLog extends Model
{
    use HasFactory;

    protected $table = 'validation_logs';

    protected $fillable = [
        'fishing_trip_id',
        'validator_id',
        'from_status',
        'to_status',
        'notes',
    ];

    /**
     * Relasi ke trip penangkapan yang divalidasi
     */
    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }

    /**
     * Relasi ke petugas validator
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}
