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
    
    $reports = Report::with('user')
        ->latest()
        // 1. Search filter: Checks title or description
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        })
        // 2. Severity filter: Exact match
        ->when($request->severity, function ($query, $severity) {
            $query->where('severity', $severity);
        })
        ->paginate(15);

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
