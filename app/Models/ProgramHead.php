<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramHead extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'major_id', 'academic_year_id'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
