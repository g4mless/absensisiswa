<?php

use App\Models\StudentPkl;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — PKL Realtime GPS (PRD pasal 13)
|--------------------------------------------------------------------------
| Channel: pkl.student.{studentPklId}
| Diizinkan: pemilik siswa_pkl, pembimbingnya, atau admin.
*/

Broadcast::channel('pkl.student.{studentPklId}', function ($user, $studentPklId) {
    if (! $user) {
        return false;
    }

    // Admin: boleh memantau semua.
    if (($user->role ?? null) === 'admin') {
        return true;
    }

    // Fallback key pkl.student.s{studentId} (tanpa record student_pkl ACTIVE).
    if (is_string($studentPklId) && str_starts_with($studentPklId, 's')) {
        $studentId = (int) substr($studentPklId, 1);

        // Pemilik: user siswa_pkl dengan student.id yang sama.
        if (($user->role ?? null) === 'siswa_pkl' && (int) ($user->student?->id) === $studentId) {
            return true;
        }

        // Pembimbing: guru yang membimbing student_pkl milik student tersebut.
        if ($user->teacher) {
            return StudentPkl::where('student_id', $studentId)
                ->where('pembimbing_id', $user->teacher->id)
                ->exists();
        }

        return false;
    }

    $pkl = StudentPkl::find($studentPklId);
    if (! $pkl) {
        return false;
    }

    // Pemilik.
    if (($user->role ?? null) === 'siswa_pkl' && (int) ($user->student?->id) === (int) $pkl->student_id) {
        return true;
    }

    // Pembimbing yang ditugaskan.
    if ($user->teacher && (int) $pkl->pembimbing_id === (int) $user->teacher->id) {
        return true;
    }

    return false;
});
