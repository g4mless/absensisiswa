<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRD pasal 12/15: student_pkl.student_id wajib UNIQUE
     * (satu siswa = satu record PKL, cascadeOnDelete).
     * Dibuat terpisah agar idempoten: dilewati bila constraint
     * sudah ada (mis. hasil create pada instalasi baru).
     */
    public function up(): void
    {
        if ($this->hasStudentUnique()) {
            return;
        }

        Schema::table('student_pkl', function (Blueprint $table) {
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        if (! $this->hasStudentUnique()) {
            return;
        }

        Schema::table('student_pkl', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
        });
    }

    private function hasStudentUnique(): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            return (bool) DB::selectOne(
                "SELECT 1 FROM pg_indexes WHERE schemaname NOT IN ('pg_catalog', 'information_schema')"
                ." AND tablename = 'student_pkl' AND indexdef ILIKE '%UNIQUE%student_id%'"
            );
        }

        // mysql / mariadb / sqlite fallback via information_schema
        try {
            return (bool) DB::selectOne(
                "SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE()"
                ." AND TABLE_NAME = 'student_pkl' AND COLUMN_NAME = 'student_id' AND NON_UNIQUE = 0"
            );
        } catch (\Throwable) {
            return false;
        }
    }
};
