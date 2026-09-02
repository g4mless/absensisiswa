<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nip'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
            ->withPivot('class_id')
            ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function programHead()
    {
        return $this->hasOne(ProgramHead::class);
    }

    public function getNameAttribute()
    {
        return $this->user->name ?? '-';
    }

}
