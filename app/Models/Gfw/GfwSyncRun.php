<?php

namespace App\Models\Gfw;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GfwSyncRun extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'gfw';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gfw_sync_runs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'aoi',
        'date_from',
        'date_to',
        'dataset',
        'endpoint',
        'records_found',
        'records_saved',
        'status',
        'error_message',
        'started_at',
        'finished_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',
            'records_found' => 'integer',
            'records_saved' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
