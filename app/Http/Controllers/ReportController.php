<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Transaction;

class ReportController extends Controller
{
    public function showReports()
    {
        $reports = Report::all();

        return response()->json([
            'success' => true,
            'message' => 'All Report Transactions',
            'data' => $reports
        ]);
    }
}
