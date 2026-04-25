<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    /**
     * @var array<string, int>
     */
    private const SEVERITY_POINTS = [
        'Low' => 10,
        'Medium' => 25,
        'High' => 50,
        'Critical' => 100,
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function createReport(array $data): Report
    {
        $user = Auth::user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $program = Program::findOrFail($data['program_id']);

        // 2. Calculate points using the program's multiplier
        $basePoints = self::SEVERITY_POINTS[$data['severity']] ?? 0;
        $finalPoints = (int) ($basePoints * $program->bounty_multiplier);

        // 3. Create the report linked to the program
        $report = $user->reports()->create($data);

        // 4. Reward the user
        $user->increment('reputation', $finalPoints);

        return $report;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateReport(Report $report, array $data): Report
    {
        if (isset($data['status'])) {
            $report = $this->transitionStatus($report, $data['status']);
            unset($data['status']);
        }

        if (! empty($data)) {
            $report->update($data);
        }

        return $report->refresh();
    }

    /**
     * List reports with optional user-based scoping.
     *
     * @param  array<string,mixed>  $filters
     */
    public function listReports(array $filters, ?User $user = null, int $perPage = 15)
    {
        $user = $user ?? Auth::user();

        $query = Report::with(['user', 'program'])
            ->latest()
            ->filter($filters);

        if ($user !== null && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query->paginate($perPage);
    }

    public function transitionStatus(Report $report, string $newStatus): Report
    {
        // Business Rule: You can't un-patch a bug once it's closed
        if ($report->status === 'Patched' && $newStatus === 'Open') {
            throw new HttpResponseException(
                response()->json(['message' => 'Cannot re-open a patched vulnerability.'], 422)
            );
        }

        $report->update(['status' => $newStatus]);

        // If it was patched, maybe send a notification or extra points?
        if ($newStatus === 'Patched') {
            // Logic for notifications will go here in Week 6 (Queues)
        }

        return $report;
    }
}
