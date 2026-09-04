<?php

namespace App\Imports;

use App\Models\ClassModel;
use App\Models\HomeroomTeacher;
use App\Models\Major;
use App\Models\ProgramHead;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class TeacherImport
{
    public function import(UploadedFile $file, array $selectedSheets): array
    {
        if (! in_array('distribusi', $selectedSheets, true)) {
            throw new RuntimeException('Pilih worksheet distribusi.');
        }

        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly($selectedSheets);
        $workbook = $reader->load($file->getRealPath());
        $distribution = $workbook->getSheetByName('distribusi');

        if (! $distribution) {
            throw new RuntimeException('Worksheet distribusi tidak ditemukan.');
        }

        $distributionData = $this->readDistribution($distribution);
        $teachers = [];

        DB::transaction(function () use ($workbook, $selectedSheets, $distributionData, &$teachers) {
            $teachers = $this->saveTeachers($distributionData['teachers']);

            if (in_array('Walas 26-27', $selectedSheets, true)) {
                $this->saveWalas($workbook->getSheetByName('Walas 26-27'), $distributionData['nips']);
            }

            foreach (['10', '11'] as $grade) {
                if (in_array($grade, $selectedSheets, true) && $workbook->getSheetByName($grade)) {
                    $this->saveSchedules($workbook->getSheetByName($grade), $grade, $distributionData['codes']);
                }
            }
        });

        return ['teachers' => count($teachers)];
    }

    private function readDistribution($sheet): array
    {
        $teachers = [];
        $nips = [];
        $codes = [];

        for ($row = 9; $row <= 130; $row++) {
            $number = $sheet->getCell("A$row")->getValue();
            $name = trim((string) $sheet->getCell("B$row")->getValue());
            $nextValue = trim((string) $sheet->getCell('B'.($row + 1))->getValue());

            if (is_numeric($number) && $name !== '' && str_starts_with($nextValue, 'NIP')) {
                $nip = preg_replace('/^NIP(?:PPK)?\.?/i', '', $nextValue);
                $key = $this->normalizeName($name);
                $teachers[$key] = ['name' => $name, 'nip' => $nip];
                $nips[$key] = $nip;
            }

            $code = trim((string) $sheet->getCell("D$row")->getValue());
            if ($code !== '' && isset($teachers[$this->normalizeName($name)])) {
                $codes[$code] = $teachers[$this->normalizeName($name)];
            } elseif ($code !== '') {
                $previous = array_key_last($teachers);
                if ($previous !== null) {
                    $codes[$code] = $teachers[$previous];
                }
            }
        }

        return compact('teachers', 'nips', 'codes');
    }

    private function saveTeachers(array $teacherData): array
    {
        $teachers = [];

        foreach ($teacherData as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['nip']],
                [
                    'name' => $data['name'],
                    'role' => 'guru',
                    'password' => Hash::make($data['nip']),
                ]
            );
            $user->update(['name' => $data['name'], 'role' => 'guru']);

            $teachers[$data['nip']] = Teacher::updateOrCreate(
                ['nip' => $data['nip']],
                ['user_id' => $user->id]
            );
        }

        return $teachers;
    }

    private function saveWalas($sheet, array $nips): void
    {
        if (! $sheet) {
            return;
        }

        for ($row = 3; $row <= 13; $row++) {
            foreach ([['E', 'F'], ['G', 'H'], ['I', 'J']] as [$classColumn, $teacherColumn]) {
                $className = trim((string) $sheet->getCell($classColumn.$row)->getValue());
                $teacherName = trim((string) $sheet->getCell($teacherColumn.$row)->getValue());

                if ($className === '' || $teacherName === '' || ! preg_match('/^(10|11|12)\s+(.+?)\s+(\S+)$/i', $className, $parts)) {
                    continue;
                }

                $teacher = $this->teacherByName($teacherName, $nips);
                if (! $teacher) {
                    throw new RuntimeException("Guru wali kelas tidak ditemukan: $teacherName");
                }

                $class = $this->class(['10' => 'X', '11' => 'XI', '12' => 'XII'][$parts[1]], $parts[2], $parts[3]);
                HomeroomTeacher::updateOrCreate(['class_id' => $class->id], ['teacher_id' => $teacher->id]);
            }
        }

        for ($row = 3; $row <= 8; $row++) {
            $majorCode = strtoupper(trim((string) $sheet->getCell("C$row")->getValue()));
            $teacherName = trim((string) $sheet->getCell("B$row")->getValue());
            if ($majorCode === '' || $teacherName === '') {
                continue;
            }

            $teacher = $this->teacherByName($teacherName, $nips);
            if (! $teacher) {
                throw new RuntimeException("Kaprog tidak ditemukan: $teacherName");
            }

            $major = Major::firstOrCreate(['code' => $majorCode], ['name' => $majorCode]);
            ProgramHead::updateOrCreate(['major_id' => $major->id], ['teacher_id' => $teacher->id]);
        }
    }

    private function saveSchedules($sheet, string $grade, array $codes): void
    {
        $day = null;

        for ($row = 8; $row <= $sheet->getHighestRow(); $row++) {
            $dayValue = strtoupper(trim((string) $sheet->getCell("A$row")->getValue()));
            if ($dayValue !== '') {
                $day = $this->day($dayValue);
            }

            $time = $this->timeRange((string) $sheet->getCell("C$row")->getValue());
            if (! $day || ! $time) {
                continue;
            }

            for ($column = 4; $column <= 34; $column += 3) {
                $code = trim((string) $sheet->getCellByColumnAndRow($column, $row)->getValue());
                $subjectName = trim((string) $sheet->getCellByColumnAndRow($column + 1, $row)->getValue());
                $room = trim((string) $sheet->getCellByColumnAndRow($column + 2, $row)->getValue());

                if ($code === '' || $subjectName === '' || ! isset($codes[$code])) {
                    continue;
                }

                $header = trim((string) $sheet->getCellByColumnAndRow($column + 1, 7)->getValue());
                if ($header === '') {
                    continue;
                }

                $class = $this->classFromHeader($grade, $header);
                $subject = Subject::firstOrCreate(['name' => $this->subjectName($subjectName)], ['code' => null]);
                $teacher = Teacher::where('nip', $codes[$code]['nip'])->firstOrFail();

                TeacherSubject::updateOrCreate([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                ]);
                Schedule::updateOrCreate(
                    ['class_id' => $class->id, 'day' => $day, 'start_time' => $time[0], 'end_time' => $time[1]],
                    ['subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'room' => $room ?: null]
                );
            }
        }
    }

    private function classFromHeader(string $grade, string $header): ClassModel
    {
        $header = strtoupper(trim($header));
        if (! preg_match('/^(.+?)(\d+)$/', $header, $parts)) {
            $parts = [null, $header, '1'];
        }

        return $this->class($grade, $parts[1], $parts[2]);
    }

    private function class(string $grade, string $majorCode, string $section): ClassModel
    {
        $grade = ['10' => 'X', '11' => 'XI', '12' => 'XII'][$grade] ?? strtoupper($grade);
        $majorCode = strtoupper(trim($majorCode));
        $major = Major::firstOrCreate(['code' => $majorCode], ['name' => $majorCode]);

        return ClassModel::firstOrCreate([
            'major_id' => $major->id,
            'grade' => strtoupper($grade),
            'section' => $section,
        ]);
    }

    private function teacherByName(string $name, array $nips): ?Teacher
    {
        $normalized = $this->normalizeName($name);
        $nip = $nips[$normalized] ?? null;

        if (! $nip) {
            foreach ($nips as $candidateName => $candidateNip) {
                if (levenshtein($normalized, $candidateName) <= 2) {
                    $nip = $candidateNip;
                    break;
                }
            }
        }

        return $nip ? Teacher::where('nip', $nip)->first() : null;
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(trim(explode(',', $name)[0]));
        return preg_replace('/\s+/', ' ', preg_replace('/[^a-z\s]/', '', $name));
    }

    private function subjectName(string $name): string
    {
        $aliases = [
            'B. INGGRIS' => 'Bahasa Inggris',
            'B. INDONESIA' => 'Bahasa Indonesia',
            'PAI' => 'Pendidikan Agama dan Budi Pekerti',
            'PJOK' => 'Pendidikan Jasmani Olahraga dan Kesehatan',
            'IPAS' => 'Ilmu Pengetahuan Alam dan Sosial',
            'PEND. PANCASILA' => 'Pendidikan Pancasila',
        ];

        return $aliases[strtoupper($name)] ?? ucwords(strtolower($name));
    }

    private function day(string $day): ?string
    {
        return [
            'SENIN' => 'Monday', 'SELASA' => 'Tuesday', 'RABU' => 'Wednesday',
            'KAMIS' => 'Thursday', 'JUMAT' => 'Friday',
        ][$day] ?? null;
    }

    private function timeRange(string $value): ?array
    {
        if (! preg_match('/(\d{1,2})[\.:](\d{2})\s*-\s*(\d{1,2})[\.:](\d{2})/', $value, $parts)) {
            return null;
        }

        return [sprintf('%02d:%02d:00', $parts[1], $parts[2]), sprintf('%02d:%02d:00', $parts[3], $parts[4])];
    }
}
