<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProgramService
{
    public function listPrograms(): Collection
    {
        return Cache::remember('programs_all', now()->addDay(), function () {
            return Program::query()
                ->orderByDesc('id')
                ->get();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('programs_all');
    }
}