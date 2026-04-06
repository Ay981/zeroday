<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Gate;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReportRequest;
use App\Http\Resources\ReportResource;


class ReportController extends Controller
{
    public function index(Request $request)
{
    $this->authorize('viewAny', Report::class);
// We start from the authenticated user and drill down into their reports
    $reports = $request->user()
    ->reports()
    ->with('user')
    ->get();

    return response()->json($reports);
}


public function store(StoreReportRequest $request)
{
    Gate::authorize('create', Report::class);

    $report = Report::create([
        ...$request->validated(),
        'user_id' => $request->user()->id,
        'status'  => 'Open',
    ]);


    return  ReportResource::collection([$report]);
}
public function show(Report $report)
{
    Gate::authorize('view', $report);

return new ReportResource($report->load('user'));}
}