<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\Major;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Row;

class StudentSheetImport implements OnEachRow, WithEvents
{
    protected array $students = [];

    public function onRow(Row $row): void
    {
        if ($row->getIndex() < 7) {
            return;
        }

        $cellIterator = $row->getDelegate()->getCellIterator('A', 'AQ');
        $cellIterator->setIterateOnlyExistingCells(false);
        $cells = iterator_to_array($cellIterator);
        $nisn = $this->cellValue($cells['E'] ?? null);
        $nama = $this->cellValue($cells['B'] ?? null);
        $rombel = $this->cellValue($cells['AQ'] ?? null);

        if (empty($nisn) || empty($nama) || empty($rombel) || str_starts_with($nama, '=')) {
            return;
        }

        $this->students[$nisn] = [
            'name' => $nama,
            'nis' => $nisn,
            'rombel' => $rombel,
            'phone' => $this->cellValue($cells['T'] ?? null)
                ?: $this->cellValue($cells['S'] ?? null),
            'address' => $this->cellValue($cells['J'] ?? null),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function () {
                $this->insertStudents($this->students);
            },
        ];
    }

    protected function getOrCreateClass(string $rombel): int
    {
        preg_match('/^(\d+)\s+([A-Za-z0-9]+)(?:\s+(.+))?$/', trim($rombel), $parts);
        $grade = $parts[1] ?? 'X';
        $code = strtoupper($parts[2] ?? $rombel);
        $section = trim($parts[3] ?? '1');

        $major = Major::firstOrCreate(
            ['code' => $code],
            ['name' => $code]
        );

        $class = ClassModel::firstOrCreate(
            ['major_id' => $major->id, 'grade' => $grade, 'section' => $section]
        );

        return $class->id;
    }

    protected function clean($value): string
    {
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || $value === '#N/A' || $value === 'null') {
                return '';
            }
        }

        return (string) $value;
    }

    protected function cellValue($cell): string
    {
        if ($cell === null) {
            return '';
        }

        $value = $cell->getValue();
        if (is_string($value) && str_starts_with($value, '=')) {
            $value = $cell->getOldCalculatedValue();
        }

        return $this->clean($value);
    }

    protected function insertStudents(array $students): void
    {
        if ($students === []) {
            return;
        }

        DB::transaction(function () use ($students) {
            foreach ($students as $student) {
                $classId = $this->getOrCreateClass($student['rombel']);
                $existingStudent = DB::table('students')->where('nis', $student['nis'])->first();
                $now = now();

                if ($existingStudent) {
                    DB::table('users')->where('id', $existingStudent->user_id)->update([
                        'name' => $student['name'],
                        'username' => $student['nis'],
                        'updated_at' => $now,
                    ]);
                    DB::table('students')->where('id', $existingStudent->id)->update([
                        'class_id' => $classId,
                        'phone' => $student['phone'] ?: null,
                        'address' => $student['address'] ?: null,
                        'updated_at' => $now,
                    ]);
                    continue;
                }

                $userId = DB::table('users')->insertGetId([
                    'name' => $student['name'],
                    'username' => $student['nis'],
                    'role' => 'siswa',
                    // Password is upgraded to bcrypt after the student's first login.
                    'password' => $student['nis'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('students')->insert([
                    'user_id' => $userId,
                    'nis' => $student['nis'],
                    'class_id' => $classId,
                    'phone' => $student['phone'] ?: null,
                    'address' => $student['address'] ?: null,
                    'is_pkl' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
