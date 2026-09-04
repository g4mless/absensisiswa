<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminReportExport implements Export, FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters)
    {
    }

    public function collection(): Collection
    {
        return Attendance::with(['student.user', 'student.class', 'attendanceSession'])
            ->when($this->filters['start_date'] ?? null, fn ($query, $date) =>
                $query->whereHas('attendanceSession', fn ($session) => $session->whereDate('date', '>=', $date)))
            ->when($this->filters['end_date'] ?? null, fn ($query, $date) =>
                $query->whereHas('attendanceSession', fn ($session) => $session->whereDate('date', '<=', $date)))
            ->when($this->filters['class_id'] ?? null, fn ($query, $classId) =>
                $query->whereHas('attendanceSession', fn ($session) => $session->where('class_id', $classId)))
            ->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Siswa', 'Kelas', 'Status', 'Jam Masuk', 'Catatan'];
    }

    public function map(mixed $attendance): array
    {
        return [
            $attendance->attendanceSession?->date,
            $attendance->student?->user?->name ?? '-',
            $attendance->student?->class?->name ?? '-',
            $attendance->status,
            $attendance->check_in_time,
            $attendance->notes,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
