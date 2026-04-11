<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Support\Facades\Cache;

class ProgramService
{
    /**
     * List all programs with a 24-hour cache layer.
     */
    public function listPrograms()
    {
        // Key: 'programs_all'
        // Time: 86400 seconds (24 hours)
        return Cache::remember('programs_all', now()->addDay(), function () {
            // This code ONLY runs if the cache is empty
            \Log::info("CACHE MISS: Fetching programs from Database.");
            return Program::all();
        });
    }
}