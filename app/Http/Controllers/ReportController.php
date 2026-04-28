<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        Gate::authorize('viewAny', Report::class);

        $filters = $request->only(['search', 'severity']);
        $perPage = $request->input('per_page', 15);
        $reports = $service->listReports($filters, $perPage);

        return ReportResource::collection($reports);
    }

    public function store(StoreReportRequest $request, ReportService $service)
{
    // Auth Check
    Gate::authorize('create', Report::class);

    // The Form Request validated() method automatically includes the file 
    // because you defined 'evidence_image' in your rules!
    $report = $service->createReport($request->validated());

    return new ReportResource($report->load('user', 'program'));
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
