<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index()
    {
        $classes = ClassModel::orderBy('name')->get();
        $reportData = ['students' => []];
        return view('admin.reports.index', compact('classes', 'reportData'));
    }
}
