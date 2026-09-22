<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceImport extends Model
{
    protected $table = 'reference_imports';

    protected $fillable = [
        'reference_type',
        'source',
        'source_version',
        'imported_at',
        'total_records',
        'total_inserted',
        'total_updated',
        'total_deactivated',
        'status',
        'notes',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'total_records' => 'integer',
        'total_inserted' => 'integer',
        'total_updated' => 'integer',
        'total_deactivated' => 'integer',
    ];
}
