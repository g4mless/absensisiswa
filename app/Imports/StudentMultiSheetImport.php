<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Daftar Peserta Didik' => new StudentSheetImport,
        ];
    }
}
