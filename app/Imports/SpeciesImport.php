<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SpeciesImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // Data di-passing secara mentah dalam bentuk array
        // untuk divalidasi dan di-preview oleh Controller
        return $array;
    }
}
