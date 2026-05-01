<?php

use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/
pest()
    ->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Disable Rate Limiting for all tests
|--------------------------------------------------------------------------
*/
pest()->beforeEach(function () {
    foreach (['auth', 'api', 'uploads'] as $limiter) {
        RateLimiter::for($limiter, fn () => Limit::none());
    }
})->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/
expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/
function testProgram(float $multiplier = 1.0): \App\Models\Program
{
    return \App\Models\Program::unguarded(
        fn () => \App\Models\Program::create([
            'name'             => 'Test Program ' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4)),
            'slug'             => 'test-program-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'description'      => 'Test program description',
            'bounty_multiplier' => $multiplier,
        ])
    );
}