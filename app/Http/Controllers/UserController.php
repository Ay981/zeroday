<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        // Optimized Database Aggregation
        return response()->json([
            'stats' => [
                'total_reports' => $user->reports()->count(),
                'critical_count' => $user->reports()->where('severity', 'Critical')->count(),
                'open_bugs' => $user->reports()->where('status', 'Open')->count(),
            ],
            'recent_activity' => ReportResource::collection(
                $user->reports()->latest()->take(5)->get()
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
