<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SpeciesTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function array(): array
    {
        return []; // Template kosong, hanya butuh header
    }

    public function headings(): array
    {
        return [
            'Kode FAO (3-Alpha)',
            'Kelompok Ikan',
            'Nama Indonesia',
            'Nama Lokal Aceh',
            'Nama Ilmiah / Latin',
            'Famili Taksonomi',
            'Nama Bahasa Inggris',
            'Status Konservasi',
            'Status Aktif',
        ];
    }
}
