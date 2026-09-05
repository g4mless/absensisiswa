<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            foreach (['10' => 'X', '11' => 'XI', '12' => 'XII'] as $oldGrade => $grade) {
                DB::table('classes')->where('grade', $oldGrade)->orderBy('id')->eachById(function ($source) use ($grade) {
                    $target = DB::table('classes')
                        ->where('major_id', $source->major_id)
                        ->where('grade', $grade)
                        ->where('section', $source->section)
                        ->first();

                    if (! $target) {
                        DB::table('classes')->where('id', $source->id)->update(['grade' => $grade]);
                        return;
                    }

                    DB::table('students')->where('class_id', $source->id)->update(['class_id' => $target->id]);
                    DB::table('attendance_sessions')->where('class_id', $source->id)->update(['class_id' => $target->id]);

                    foreach (DB::table('teacher_subjects')->where('class_id', $source->id)->get() as $item) {
                        $duplicate = DB::table('teacher_subjects')->where([
                            'teacher_id' => $item->teacher_id,
                            'subject_id' => $item->subject_id,
                            'class_id' => $target->id,
                        ])->exists();
                        if ($duplicate) {
                            DB::table('teacher_subjects')->where('id', $item->id)->delete();
                        } else {
                            DB::table('teacher_subjects')->where('id', $item->id)->update(['class_id' => $target->id]);
                        }
                    }

                    foreach (DB::table('schedules')->where('class_id', $source->id)->get() as $item) {
                        $duplicate = DB::table('schedules')->where([
                            'class_id' => $target->id,
                            'day' => $item->day,
                            'start_time' => $item->start_time,
                            'end_time' => $item->end_time,
                        ])->exists();
                        if ($duplicate) {
                            DB::table('schedules')->where('id', $item->id)->delete();
                        } else {
                            DB::table('schedules')->where('id', $item->id)->update(['class_id' => $target->id]);
                        }
                    }

                    $homeroom = DB::table('homeroom_teachers')->where('class_id', $source->id)->first();
                    if ($homeroom) {
                        if (DB::table('homeroom_teachers')->where('class_id', $target->id)->exists()) {
                            DB::table('homeroom_teachers')->where('id', $homeroom->id)->delete();
                        } else {
                            DB::table('homeroom_teachers')->where('id', $homeroom->id)->update(['class_id' => $target->id]);
                        }
                    }

                    DB::table('classes')->where('id', $source->id)->delete();
                });
            }
        });
    }

    public function down(): void
    {
        // Grade values are intentionally canonicalized and cannot be safely reversed.
    }
};
