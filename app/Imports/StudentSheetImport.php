<?php

namespace App\Imports;

use App\Models\ClassModel;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Row;

class StudentSheetImport implements OnEachRow, WithEvents
{
    protected string $sheetName;

    protected array $students = [];

    public function __construct(string $sheetName)
    {
        $this->sheetName = $sheetName;
    }

    public function onRow(Row $row): void
    {
        if ($row->getIndex() === 1) {
            return;
        }

        $cellIterator = $row->getDelegate()->getCellIterator('A', 'O');
        $cellIterator->setIterateOnlyExistingCells(false);
        $cells = array_values(iterator_to_array($cellIterator));
        // D, F, and O are the fixed columns in the Dapodik export.
        $nisn = $this->cellValue($cells[3] ?? null);
        $nama = $this->cellValue($cells[5] ?? null);

        if (empty($nisn) || empty($nama) || str_starts_with($nama, '=')) {
            return;
        }

        $this->students[$nisn] = [
            'name' => $nama,
            'username' => $nisn,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function () {
                $this->insertStudents($this->students, $this->getOrCreateClass());
            },
        ];
    }

    protected function getOrCreateClass(): int
    {
        $className = 'X '.$this->sheetName;

        $class = ClassModel::firstOrCreate(
            ['name' => $className, 'major' => $this->sheetName],
            ['name' => $className, 'major' => $this->sheetName]
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

    protected function insertStudents(array $students, int $classId): void
    {
        if ($students === []) {
            return;
        }

        $now = now();
        $usernames = array_keys($students);
        $existing = DB::table('users')
            ->whereIn('username', $usernames)
            ->pluck('username')
            ->all();
        $existing = array_fill_keys($existing, true);

        $users = [];
        foreach ($students as $student) {
            if (isset($existing[$student['username']])) {
                continue;
            }

            // Imported passwords are migrated to bcrypt on the first login.
            $users[] = $student + [
                'role' => 'siswa',
                'password' => $student['username'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($users === []) {
            return;
        }

        DB::transaction(function () use ($users, $classId) {
            DB::table('users')->insert($users);

            $ids = DB::table('users')
                ->whereIn('username', array_column($users, 'username'))
                ->pluck('id', 'username');

            $studentRows = [];
            foreach ($users as $user) {
                $studentRows[] = [
                    'user_id' => $ids[$user['username']],
                    'nis' => $user['username'],
                    'class_id' => $classId,
                    'is_pkl' => false,
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ];
            }

            DB::table('students')->insert($studentRows);
        });
    }
}
