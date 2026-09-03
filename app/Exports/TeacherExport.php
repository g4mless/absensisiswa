<?php

namespace App\Exports;

use App\Models\Teacher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherExport implements Export, FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Collection
    {
        return Teacher::with(['user', 'subjects'])->orderBy('nip')->get();
    }

    public function headings(): array
    {
        return ['NIP', 'Nama', 'Mata Pelajaran'];
    }

    public function map(mixed $teacher): array
    {
        return [
            $teacher->nip,
            $teacher->user->name,
            $teacher->subjects->pluck('name')->implode(', '),
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
