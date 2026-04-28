<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProgramService
{
    public function listPrograms(): Collection
    {
        $cached = Cache::remember('programs_all', now()->addDay(), function () {
            return Program::query()
                ->orderByDesc('id')
                ->get()
                ->toArray();
        });

        if (is_array($cached)) {
            return Program::hydrate($cached)->sortByDesc('id')->values();
        }

        if ($cached instanceof Collection) {
            return $cached;
        }

        Cache::forget('programs_all');
        return Program::query()->orderByDesc('id')->get();
    }

    public function clearCache(): void
    {
        Cache::forget('programs_all');
    }
}