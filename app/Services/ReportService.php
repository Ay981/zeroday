<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Auth\AuthenticationException;
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

        $points = self::SEVERITY_POINTS[$data['severity'] ?? ''] ?? 0;

        $report = $user->reports()->create($data);

        $user->increment('reputation', $points);

        return $report;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateReport(Report $report, array $data): Report
    {
        $report->update($data);

        return $report->refresh();
    }
}
