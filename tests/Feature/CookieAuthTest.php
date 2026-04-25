<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in users and persists session cookie for v1 routes', function () {
    $password = 'secret-password';
    $user = User::factory()->create(['password' => bcrypt($password), 'email' => 'cookie@example.test']);

    // Use normal form POST so the test client preserves session cookies between requests
    $this->post('/api/v1/login', [
        'email' => $user->email,
        'password' => $password,
    ])->assertStatus(200)->assertJsonStructure(['token']);

    // Subsequent request should be authenticated via session cookie
    $this->get('/api/v1/user')
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $user->id]);
});
