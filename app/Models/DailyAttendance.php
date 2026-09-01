<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'date', 'status', 'check_in_time',
        'latitude', 'longitude', 'accuracy', 'source',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
