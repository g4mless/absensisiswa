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

    /** Penempatan PKL yang dibimbing guru ini (PRD pasal 12: student_pkl.pembimbing_id). */
    public function supervisedPkl()
    {
        return $this->hasMany(StudentPkl::class, 'pembimbing_id');
    }

    public function supervisedPkls()
    {
        return $this->hasMany(StudentPkl::class, 'pembimbing_id');
    }

    public function getNameAttribute()
    {
        return $this->user->name ?? '-';
    }

}
