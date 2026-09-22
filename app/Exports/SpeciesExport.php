<?php

namespace App\Exports;

use App\Models\Species;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpeciesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $search;

    protected $filterStatus;

    protected $filterFishStat;

    protected $filterIsscaap;

    public function __construct($search = null, $filterStatus = null, $filterFishStat = null, $filterIsscaap = null)
    {
        $this->search = $search;
        $this->filterStatus = $filterStatus;
        $this->filterFishStat = $filterFishStat;
        $this->filterIsscaap = $filterIsscaap;
    }

    public function collection(): Enumerable
    {
        return Species::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('fao_code', 'like', "%{$this->search}%")
                        ->orWhere('scientific_name', 'like', "%{$this->search}%")
                        ->orWhere('english_name', 'like', "%{$this->search}%")
                        ->orWhere('family', 'like', "%{$this->search}%")
                        ->orWhere('local_name_id', 'like', "%{$this->search}%")
                        ->orWhere('local_name_aceh', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus !== null && $this->filterStatus !== '', function ($q) {
                if (strtolower($this->filterStatus) === 'active') {
                    $q->where('is_active', 1);
                } elseif (strtolower($this->filterStatus) === 'inactive') {
                    $q->where('is_active', 0);
                }
            })
            ->when($this->filterFishStat !== null && $this->filterFishStat !== '', function ($q) {
                if (strtolower($this->filterFishStat) === 'yes') {
                    $q->where('is_statistical_item', 1);
                } elseif (strtolower($this->filterFishStat) === 'no') {
                    $q->where('is_statistical_item', 0);
                }
            })
            ->when($this->filterIsscaap !== null && $this->filterIsscaap !== '', function ($q) {
                $q->where('isscaap_code', $this->filterIsscaap);
            })
            ->orderBy('local_name_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Species ID',
            'FAO Code',
            'Taxonomic Code',
            'ISSCAAP',
            'Scientific Name',
            'Author',
            'Family',
            'Higher Taxa',
            'English Name',
            'French Name',
            'Spanish Name',
            'Taxon Level',
            'Functional Group',
            'FishStat',
            'ASFIS Version',
            'ASFIS Source',
            'Nama Indonesia',
            'Variasi Nama Indonesia',
            'Kode Indonesia',
            'Status Indonesia',
            'Nama Lokal Aceh',
            'Status Aktif',
            'Urutan',
            'Catatan',
        ];
    }

    public function map($species): array
    {
        return [
            $species->id,
            $species->fao_code,
            $species->taxonomic_code,
            $species->isscaap_code,
            $species->scientific_name,
            $species->author,
            $species->family,
            $species->higher_taxa,
            $species->english_name,
            $species->french_name,
            $species->spanish_name,
            $species->taxon_level,
            $species->functional_group,
            $species->is_statistical_item ? 'YES' : 'NO',
            $species->fao_version,
            $species->fao_source,
            $species->local_name_id,
            $species->local_name_variants,
            $species->indonesia_code,
            $species->is_indonesia ? 'YES' : 'NO',
            $species->local_name_aceh,
            $species->is_active ? 'YES' : 'NO',
            $species->sort_order,
            $species->notes,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0284c7']]],
        ];
    }
}
