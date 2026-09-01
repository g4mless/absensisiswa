<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklLocation extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'latitude', 'longitude', 'address'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
