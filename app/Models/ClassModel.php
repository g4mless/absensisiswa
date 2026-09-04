<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = ['major_id', 'grade', 'section'];

    protected $appends = ['name'];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->grade} {$this->major?->code} {$this->section}");
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function homeroomTeacher()
    {
        return $this->hasOne(HomeroomTeacher::class, 'class_id');
    }

}
