<?php

namespace App\Exports;

use App\Models\PklSupervisor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PklSupervisorExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return PklSupervisor::with(['teacher.user', 'class.major'])->orderBy('id')->get();
    }

    public function headings(): array
    {
        return ['NIP Guru', 'Nama Guru', 'Kelas'];
    }

    public function map(mixed $assignment): array
    {
        return [
            $assignment->teacher->nip,
            $assignment->teacher->user->name,
            $assignment->class->grade.' '.$assignment->class->major->code.' '.$assignment->class->section,
        ];
    }
}
