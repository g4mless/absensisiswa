<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Major;
use App\Models\Student;
use App\Models\StudentPkl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPklTest extends TestCase
{
    use RefreshDatabase;

    private function makePklStudent(): User
    {
        $major = Major::create(['name' => 'RPL', 'code' => 'RPL']);
        $class = ClassModel::create(['major_id' => $major->id, 'grade' => 'XII', 'section' => '1']);
        $user = User::create([
            'name' => 'Siswa PKL',
            'username' => 'siswapkl-001',
            'role' => 'siswa_pkl',
            'password' => 'secret123',
        ]);
        $student = Student::create([
            'user_id' => $user->id,
            'nis' => '001',
            'class_id' => $class->id,
            'is_pkl' => true,
        ]);
        StudentPkl::create([
            'student_id' => $student->id,
            'tempat_pkl' => 'PT Maju',
            'start_date' => today()->subMonth()->toDateString(),
            'end_date' => today()->addMonth()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        return $user->fresh();
    }

    public function test_non_pkl_role_is_forbidden(): void
    {
        $major = Major::create(['name' => 'RPL', 'code' => 'RPL']);
        $class = ClassModel::create(['major_id' => $major->id, 'grade' => 'X', 'section' => '1']);
        $user = User::create(['name' => 'S', 'username' => 's-1', 'role' => 'siswa', 'password' => 'secret123']);
        Student::create(['user_id' => $user->id, 'nis' => '999', 'class_id' => $class->id, 'is_pkl' => false]);

        $this->actingAs($user)->get(route('student-pkl.dashboard'))->assertForbidden();
    }

    public function test_pages_render(): void
    {
        $this->withoutVite();
        $user = $this->makePklStudent();

        foreach (['student-pkl.dashboard', 'student-pkl.attendance', 'student-pkl.location', 'student-pkl.history', 'student-pkl.profile'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_checkin_and_checkout_flow(): void
    {
        $user = $this->makePklStudent();

        $this->actingAs($user)->postJson(route('student-pkl.attendance.checkin'), [
            'latitude' => -6.2, 'longitude' => 106.8, 'accuracy' => 12.5,
        ])->assertOk()->assertJson(['message' => 'Check in PKL berhasil!']);

        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $user->student->id, 'status' => 'hadir', 'source' => 'pkl',
        ]);

        // Duplikat hari yang sama ditolak.
        $this->actingAs($user)->postJson(route('student-pkl.attendance.checkin'), [
            'latitude' => -6.2, 'longitude' => 106.8,
        ])->assertStatus(422);

        $this->actingAs($user)->postJson(route('student-pkl.attendance.checkout'), [
            'latitude' => -6.2, 'longitude' => 106.8,
        ])->assertOk();

        $this->assertNotNull($user->student->fresh()
            ? \App\Models\DailyAttendance::where('student_id', $user->student->id)->first()->check_out_time
            : null);
    }

    public function test_invalid_gps_rejected(): void
    {
        $user = $this->makePklStudent();

        $this->actingAs($user)->postJson(route('student-pkl.attendance.checkin'), [
            'latitude' => 200, 'longitude' => 106.8,
        ])->assertStatus(422);
    }

    public function test_location_send_and_throttle(): void
    {
        $user = $this->makePklStudent();

        $this->actingAs($user)->postJson(route('student-pkl.location.send'), [
            'latitude' => -6.2, 'longitude' => 106.8, 'accuracy' => 8,
        ])->assertOk();

        // Kirim kedua dalam <10 detik ditolak.
        $this->actingAs($user)->postJson(route('student-pkl.location.send'), [
            'latitude' => -6.2, 'longitude' => 106.8,
        ])->assertStatus(429);
    }

    public function test_update_profile(): void
    {
        $user = $this->makePklStudent();

        $this->actingAs($user)->put(route('student-pkl.profile.update'), [
            'name' => 'Nama Baru', 'phone' => '08123', 'address' => 'Jl. Mawar',
        ])->assertRedirect(route('student-pkl.profile'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Baru']);
        $this->assertDatabaseHas('students', ['user_id' => $user->id, 'phone' => '08123']);
    }
}
