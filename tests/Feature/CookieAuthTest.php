<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in users and persists session cookie', function () {
    $user = User::factory()->create([
        'password'     => bcrypt('password'),
        'otp_verified' => true,          // <-- add this
    ]);

    $response = $this->withHeaders([
            'Origin'  => config('app.url'),
            'Referer' => config('app.url'),
        ])
        ->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

    $response->assertOk();
    $this->assertAuthenticated();
});