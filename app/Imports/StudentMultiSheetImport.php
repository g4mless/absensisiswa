<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\Major;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentMultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'DKV'  => new StudentSheetImport('DKV'),
            'DPB'  => new StudentSheetImport('DPB'),
            'TITL' => new StudentSheetImport('TITL'),
            'TKJ'  => new StudentSheetImport('TKJ'),
            'TSM'  => new StudentSheetImport('TSM'),
            'MP'   => new StudentSheetImport('MP'),
        ];
    }
}
