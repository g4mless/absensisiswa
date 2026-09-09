<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentPkl;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;

class PklService
{
    /**
     * Ambil record PKL yang aktif untuk siswa tertentu.
     * Aktif = status ACTIVE dan (tanggal hari ini di dalam rentang
     * start_date/end_date bila tanggal diisi).
     */
    public function resolveActivePkl(Student $student, ?Carbon $date = null): ?StudentPkl
    {
        $date ??= Carbon::today();

        $pkl = $student->pkl()->active()->first();

        if (! $pkl) {
            return null;
        }

        return $this->isActiveOnDate($pkl, $date) ? $pkl : null;
    }

    /**
     * Cek apakah record PKL aktif pada tanggal tertentu.
     */
    public function isActiveOnDate(StudentPkl $pkl, ?Carbon $date = null): bool
    {
        $date ??= Carbon::today();

        if ($pkl->status !== 'ACTIVE') {
            return false;
        }

        $day = $date->copy()->startOfDay();

        if ($pkl->start_date && $day->lt($pkl->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($pkl->end_date && $day->gt($pkl->end_date->copy()->startOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Pastikan record PKL milik siswa yang bersangkutan (anti-IDOR).
     *
     * @throws AuthorizationException
     */
    public function ensureOwnedByStudent(StudentPkl $pkl, Student $student): void
    {
        if ((int) $pkl->student_id !== (int) $student->id) {
            throw new AuthorizationException('Data PKL bukan milik siswa ini.');
        }
    }

    /**
     * Sinkron flag students.is_pkl dengan keberadaan penempatan ACTIVE.
     * Dipindah dari AdminPklPlacementController agar tetap jalan
     * setelah menu pkl-placements dihapus (PRD-strict: kelola via admin/students).
     */
    public function syncStudentPklFlag(int $studentId): bool
    {
        $hasActive = StudentPkl::where('student_id', $studentId)->where('status', 'ACTIVE')->exists();
        Student::where('id', $studentId)->update(['is_pkl' => $hasActive]);

        return $hasActive;
    }
}
