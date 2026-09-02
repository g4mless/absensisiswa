<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Student::with(['user', 'class'])->orderBy('nis')->get();
    }

    public function headings(): array
    {
        return ['NIS', 'Nama', 'Kelas', 'Telepon', 'Alamat', 'Role', 'Status PKL'];
    }

    public function map($student): array
    {
        return [
            $student->nis,
            $student->user->name,
            $student->class->name ?? '-',
            $student->phone,
            $student->address,
            $student->user->role,
            $student->is_pkl ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
