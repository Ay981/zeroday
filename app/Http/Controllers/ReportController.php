<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Gate;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $reports = $request->user()
            ->reports()
            ->with('user')
            ->get();

        return ReportResource::collection($reports);
    }

    public function store(StoreReportRequest $request)
    {
        Gate::authorize('create', Report::class);

        $report = Report::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => 'Open',
        ]);

        return new ReportResource($report->load('user'));
    }

    public function show(Report $report)
    {
        Gate::authorize('view', $report);

        return new ReportResource($report->load('user'));
    }

    public function update(UpdateReportRequest $request, Report $report)
    {

        $report->update($request->validated());

        return new ReportResource($report->load('user'));
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
