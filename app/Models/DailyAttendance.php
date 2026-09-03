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
        'gps_samples', 'sample_count', 'unique_coordinates',
        'duplicate_ratio', 'max_spread_meters', 'risk_score',
        'is_location_suspicious', 'location_flags',
    ];

    protected $casts = [
        'gps_samples' => 'array',
        'location_flags' => 'array',
        'is_location_suspicious' => 'boolean',
        'duplicate_ratio' => 'float',
        'max_spread_meters' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
