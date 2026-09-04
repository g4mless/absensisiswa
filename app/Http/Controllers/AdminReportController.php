<?php

namespace App\Http\Controllers;

use App\Exports\AdminReportExport;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;

class AdminReportController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with('major')->orderBy('grade')->orderBy('major_id')->orderBy('section')->get();
        $reportData = ['students' => []];
        return view('admin.reports.index', compact('classes', 'reportData'));
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'format' => ['required', 'in:csv,xlsx'],
        ]);

        $format = $filters['format'];
        $writerType = $format === 'csv' ? ExcelType::CSV : ExcelType::XLSX;

        return Excel::download(
            new AdminReportExport($filters),
            "laporan_absensi.{$format}",
            $writerType
        );
    }
}
