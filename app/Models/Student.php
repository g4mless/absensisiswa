<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nis', 'class_id', 'is_pkl', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_pkl' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function pklSupervisor()
    {
        return $this->hasOne(PklSupervisor::class);
    }

    public function getNameAttribute()
    {
        return $this->user->name ?? '-';
    }

    public function getEmailAttribute()
    {
        return $this->user->email ?? '-';
    }
}
