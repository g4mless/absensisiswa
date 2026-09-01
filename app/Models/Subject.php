<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description'];

    public function teacherSubjects()
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
