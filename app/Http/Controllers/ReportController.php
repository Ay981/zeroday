<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportService;
use Gate;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        $reports = $service->listReports($request->all());

        return ReportResource::collection($reports);
    }

    public function store(StoreReportRequest $request, ReportService $service)
    {
        // 1. Authorization check
        Gate::authorize('create', Report::class);

        // 2. Delegate to Service
        $report = $service->createReport($request->validated());

        // 3. Return Response
        return new ReportResource($report->load(['user', 'program']));
    }

    public function update(UpdateReportRequest $request, Report $report, ReportService $service)
    {
        // Auth is handled by the Form Request 'authorize' method we built earlier!

        $report = $service->updateReport($report, $request->validated());

        return new ReportResource($report->load(['user', 'program']));
    }

    public function show(Report $report)
    {
        Gate::authorize('view', $report);

        return new ReportResource($report->load(['user', 'program']));
    }

    public function destroy(Report $report)
    {
        Gate::authorize('delete', $report);

        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully',
        ]);
    }
}
