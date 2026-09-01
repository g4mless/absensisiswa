<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_session_id', 'student_id', 'status',
        'check_in_time', 'check_out_time', 'latitude', 'longitude', 'notes',
    ];

    public function attendanceSession()
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getDateAttribute()
    {
        return $this->attendanceSession->date ?? null;
    }
}
