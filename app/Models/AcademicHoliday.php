<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicHoliday extends Model
{
    protected $fillable = ['academic_calendar_id', 'date', 'name'];

    protected $casts = ['date' => 'date'];

    public function calendar()
    {
        return $this->belongsTo(AcademicCalendar::class, 'academic_calendar_id');
    }
}
