<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\DailyAttendance;
use App\Models\Major;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private static int $sequence = 0;

    private function makeTeacherSession(): array
    {
        self::$sequence++;
        $suffix = self::$sequence;

        $major = Major::create(['name' => 'T'.$suffix, 'code' => 'T'.$suffix]);
        $class = ClassModel::create(['major_id' => $major->id, 'grade' => 'X', 'section' => (string) $suffix]);

        $guru = User::create([
            'name' => 'G',
            'username' => 'guru-'.$suffix.'-'.uniqid(),
            'role' => 'guru',
            'password' => 'secret123',
        ]);
        $teacher = Teacher::create(['user_id' => $guru->id, 'nip' => '111'.$suffix.uniqid()]);

        $siswa = User::create([
            'name' => 'A',
            'username' => 'siswa-'.$suffix.'-'.uniqid(),
            'role' => 'siswa',
            'password' => 'secret123',
        ]);
        $student = Student::create([
            'user_id' => $siswa->id,
            'nis' => 'nis-'.$suffix.'-'.uniqid(),
            'class_id' => $class->id,
            'is_pkl' => false,
        ]);

        $subject = Subject::create(['name' => 'K']);
        $schedule = Schedule::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day' => now()->format('l'),
            'start_time' => '01:00',
            'end_time' => '23:00',
            'room' => '1',
        ]);

        return [$guru->fresh(), $student, $schedule];
    }

    public function test_session_defaults_to_alfa_when_student_has_no_record(): void
    {
        $this->withoutVite();
        [$guru, $student, $schedule] = $this->makeTeacherSession();

        $html = $this->actingAs($guru)
            ->get(route('teacher.attendance.show', [
                'sessionId' => $schedule->id,
                'date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->getContent();

        // Radio Alfa ter-check, Hadir tidak.
        $this->assertMatchesRegularExpression(
            '/name="attendance\['.$student->id.'\]"\s+value="ALFA"\s+checked/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/name="attendance\['.$student->id.'\]"\s+value="HADIR"\s+checked/',
            $html
        );
        // Tidak ada badge/link surat di tabel (surat hanya dikelola guru via dialog).
        $this->assertStringNotContainsString('Surat terlampir', $html);
        $this->assertStringNotContainsString('Lihat surat', $html);
        $this->assertMatchesRegularExpression('/id="summary-alfa">1</', $html);
    }

    public function test_status_endpoint_returns_live_attendance(): void
    {
        [$guru, $student, $schedule] = $this->makeTeacherSession();

        DailyAttendance::create([
            'student_id' => $student->id,
            'date' => today()->toDateString(),
            'status' => 'hadir',
            'check_in_time' => '06:30:00',
            'source' => 'web',
        ]);

        $this->actingAs($guru)
            ->getJson(route('teacher.attendance.status', [
                'sessionId' => $schedule->id,
                'date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertJson([
                'attendance_completed' => true,
                'summary' => ['HADIR' => 1, 'IZIN' => 0, 'SAKIT' => 0, 'ALFA' => 0],
            ])
            ->assertJsonPath('students.0.id', $student->id)
            ->assertJsonPath('students.0.status', 'HADIR');
    }

    public function test_status_endpoint_defaults_to_alfa_without_records(): void
    {
        [$guru, $student, $schedule] = $this->makeTeacherSession();

        $this->actingAs($guru)
            ->getJson(route('teacher.attendance.status', [
                'sessionId' => $schedule->id,
                'date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertJson([
                'attendance_completed' => false,
                'summary' => ['HADIR' => 0, 'IZIN' => 0, 'SAKIT' => 0, 'ALFA' => 1],
            ])
            ->assertJsonPath('students.0.status', 'ALFA');
    }

    public function test_status_endpoint_forbids_other_teachers_session(): void
    {
        [$guru, ,] = $this->makeTeacherSession();
        [, , $otherSchedule] = $this->makeTeacherSession();

        $this->actingAs($guru)
            ->getJson(route('teacher.attendance.status', [
                'sessionId' => $otherSchedule->id,
                'date' => today()->toDateString(),
            ]))
            ->assertForbidden();
    }

    public function test_session_reflects_student_checkin_as_hadir(): void
    {
        $this->withoutVite();
        [$guru, $student, $schedule] = $this->makeTeacherSession();

        DailyAttendance::create([
            'student_id' => $student->id,
            'date' => today()->toDateString(),
            'status' => 'hadir',
            'check_in_time' => '06:30:00',
            'source' => 'web',
        ]);

        $html = $this->actingAs($guru)
            ->get(route('teacher.attendance.show', [
                'sessionId' => $schedule->id,
                'date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/name="attendance\['.$student->id.'\]"\s+value="HADIR"\s+checked/',
            $html
        );
        $this->assertMatchesRegularExpression('/id="summary-hadir">1</', $html);
    }
}
