<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicCalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')))->startOfMonth();
        $calendar = AcademicCalendar::with('holidays')->whereDate('month', $month)->first();
        $calendarDays = [];
        $day = $month->copy()->startOfWeek(Carbon::SUNDAY);
        $lastDay = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        while ($day->lte($lastDay)) {
            $calendarDays[] = $day->copy();
            $day->addDay();
        }
        $selectedHolidays = $calendar?->holidays->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))->values()->all() ?? [];
        $holidayNames = $calendar?->holidays
            ->mapWithKeys(fn ($holiday) => [$holiday->date->format('Y-m-d') => $holiday->name])
            ->all() ?? [];

        return view('admin.academic-calendar.index', compact('calendar', 'month', 'calendarDays', 'selectedHolidays', 'holidayNames'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'holiday_dates' => ['nullable', 'array'],
            'holiday_dates.*' => ['date_format:Y-m-d'],
            'holiday_names' => ['nullable', 'array'],
            'holiday_names.*' => ['nullable', 'string', 'max:255'],
        ]);
        $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        $calendar = AcademicCalendar::firstOrCreate(['month' => $month->toDateString()]);

        if ($calendar->isLocked()) {
            return back()->withErrors(['month' => 'Kalender bulan tersebut sudah terkunci sejak hari terakhir pukul 17.00.']);
        }

        $holidays = $this->parseHolidays($data['holiday_dates'] ?? [], $data['holiday_names'] ?? [], $month);

        DB::transaction(function () use ($calendar, $holidays) {
            $calendar->holidays()->delete();
            $calendar->holidays()->createMany($holidays);
        });

        return redirect()->route('admin.academic-calendar.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Kalender akademik berhasil diperbarui.');
    }

    private function parseHolidays(array $dates, array $names, Carbon $month): array
    {
        $result = [];
        foreach ($dates as $date) {
            $holidayDate = Carbon::createFromFormat('Y-m-d', $date);
            if ($holidayDate->format('Y-m') !== $month->format('Y-m')) {
                abort(422, "Tanggal libur {$date} tidak valid untuk bulan yang dipilih.");
            }

            $result[$date] = ['date' => $date, 'name' => trim($names[$date] ?? '') ?: 'Libur'];
        }

        return array_values($result);
    }
}
