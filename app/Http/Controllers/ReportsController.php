<?php

namespace App\Http\Controllers;
use App\Models\Reports;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
$reports = Reports::with('user')->get();

        // This is a "Junior" way to return data that causes N+1
        return response()->json($reports->map(function($report) {
            return [
                'id' => $report->id,
                'title' => $report->title,
                'hacker_name' => $report->user->name, // <--- THIS TRIGGERS A DB QUERY EVERY TIME!
            ];
        }));
    }
}