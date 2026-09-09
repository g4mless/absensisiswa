<?php

namespace App\Services;

use App\Models\PklLocationLog;
use App\Models\StudentPkl;
use Carbon\Carbon;
use InvalidArgumentException;

class PklLocationService
{
    /**
     * Simpan satu titik lokasi realtime untuk siswa PKL.
     * recorded_at selalu memakai server timestamp (now).
     *
     * @throws InvalidArgumentException jika lat/lng di luar range valid.
     */
    public function logLocation(
        StudentPkl $pkl,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
        ?Carbon $recordedAt = null
    ): PklLocationLog {
        if ($latitude < -90 || $latitude > 90) {
            throw new InvalidArgumentException('Latitude harus di antara -90 dan 90.');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new InvalidArgumentException('Longitude harus di antara -180 dan 180.');
        }

        if ($accuracy !== null && $accuracy < 0) {
            throw new InvalidArgumentException('Accuracy tidak boleh negatif.');
        }

        return $pkl->locationLogs()->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'recorded_at' => $recordedAt ?? Carbon::now(),
        ]);
    }

    /**
     * Hapus log lokasi yang lebih tua dari $days hari (default 30).
     * Opsional, karena data lokasi mengikuti siklus hidup siswa
     * (cascadeOnDelete) dan bukan arsip permanen.
     */
    public function prune(StudentPkl $pkl, int $days = 30): int
    {
        $cutoff = Carbon::now()->subDays($days);

        return $pkl->locationLogs()
            ->where('recorded_at', '<', $cutoff)
            ->delete();
    }
}
