<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rzwp3kZone extends Model
{
    use HasFactory;

    protected $table = 'rzwp3k_zones';

    protected $fillable = [
        'code',
        'parent_code',
        'name',
        'zone_type',
        'subzone_type',
        'description',
        'regency_id',
        'area_ha',
        'source',
        'source_document',
        'legal_basis',
        'valid_from',
        'valid_until',
        'status',
        'metadata',
        'geometry',
    ];

    protected $casts = [
        'area_ha' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'metadata' => 'array',
        'geometry' => 'array',
    ];

    /**
     * Relasi ke entitas Kabupaten/Kota di Aceh.
     */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_id');
    }

    /**
     * Scope query filter berdasarkan tipe kawasan utama (KPU, KK, KSNT, AL).
     */
    public function scopeOfZoneType(Builder $query, string $zoneType): Builder
    {
        return $query->where('zone_type', $zoneType);
    }

    /**
     * Scope query filter berdasarkan subtipe zona (e.g. KPU-PT, KK-KKP).
     */
    public function scopeOfSubzoneType(Builder $query, string $subzoneType): Builder
    {
        return $query->where('subzone_type', $subzoneType);
    }

    /**
     * Scope zona aktif secara legal.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'legal_active');
    }

    /**
     * Scope hanya zona yang memiliki geometri spasial GeoJSON.
     */
    public function scopeWithGeometry(Builder $query): Builder
    {
        return $query->whereNotNull('geometry');
    }

    /**
     * Konversi record zona menjadi GeoJSON Feature standar RFC 7946.
     *
     * @return array<string, mixed>
     */
    public function toGeoJsonFeature(): array
    {
        return [
            'type' => 'Feature',
            'id' => $this->id,
            'properties' => [
                'id' => $this->id,
                'code' => $this->code,
                'parent_code' => $this->parent_code,
                'name' => $this->name,
                'zone_type' => $this->zone_type,
                'subzone_type' => $this->subzone_type,
                'description' => $this->description,
                'regency' => $this->regency?->name,
                'area_ha' => $this->area_ha ? (float) $this->area_ha : null,
                'source' => $this->source,
                'source_document' => $this->source_document,
                'legal_basis' => $this->legal_basis,
                'status' => $this->status,
                'disclaimer' => 'Informasi ini merupakan informasi zonasi spasial. Tampilan spasial tidak dengan sendirinya menentukan status legalitas suatu aktivitas atau kapal.',
                'metadata' => $this->metadata,
            ],
            'geometry' => $this->geometry,
        ];
    }
}
