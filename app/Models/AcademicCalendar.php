<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendar extends Model
{
    protected $fillable = ['month'];

    protected $casts = ['month' => 'date'];

    public function holidays()
    {
        return $this->hasMany(AcademicHoliday::class);
    }

    public function isHoliday(CarbonInterface $date): bool
    {
        return $this->holidays()->whereDate('date', $date)->exists();
    }

    public function isLocked(?CarbonInterface $now = null): bool
    {
        $now ??= now();
        $cutoff = $this->month->copy()->endOfMonth()->setTime(17, 0);

        return $now->greaterThanOrEqualTo($cutoff);
    }
}
