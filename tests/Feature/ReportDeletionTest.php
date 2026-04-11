<?php

use App\Models\Program;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function testProgram(float $multiplier = 1.0): Program
{
    return Program::unguarded(function () use ($multiplier) {
        return Program::create([
            'name' => 'Test Program '.Str::upper(Str::random(4)),
            'slug' => 'test-program-'.Str::lower(Str::random(8)),
            'description' => 'Test program description',
            'bounty_multiplier' => $multiplier,
        ]);
    });
}

it('denies guests from viewing the feed', function (): void {
    $this->getJson('/api/v1/reports')
        ->assertUnauthorized();
});

it('allows a researcher to delete their own report', function (): void {
    $user = User::factory()->create(['role' => 'researcher']);
    $program = testProgram();

    $report = Report::factory()->create([
        'user_id' => $user->id,
        'program_id' => $program->id,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/reports/{$report->slug}")
        ->assertSuccessful();

    $this->assertSoftDeleted('reports', [
        'id' => $report->id,
    ]);
});

it('prevents a researcher from deleting another researchers report', function (): void {
    $attacker = User::factory()->create(['role' => 'researcher']);
    $victim = User::factory()->create(['role' => 'researcher']);
    $program = testProgram();

    $victimReport = Report::factory()->create([
        'user_id' => $victim->id,
        'program_id' => $program->id,
    ]);

    Sanctum::actingAs($attacker);

    $this->deleteJson("/api/v1/reports/{$victimReport->slug}")
        ->assertForbidden();
});

it('allows an admin to delete any report', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $program = testProgram();

    $randomReport = Report::factory()->create([
        'user_id' => User::factory()->create(['role' => 'researcher'])->id,
        'program_id' => $program->id,
    ]);

    Sanctum::actingAs($admin);

    $this->deleteJson("/api/v1/reports/{$randomReport->slug}")
        ->assertSuccessful();

    $this->assertSoftDeleted('reports', [
        'id' => $randomReport->id,
    ]);
});

it('prevents an admin from updating another users report', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'researcher']);
    $program = testProgram();

    $report = Report::factory()->create([
        'user_id' => $owner->id,
        'program_id' => $program->id,
    ]);

    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/reports/{$report->slug}", [
        'title' => 'Updated report title for unauthorized admin attempt',
    ])->assertForbidden();
});


it('awards 100 points for a critical vulnerability', function () {
    $user = User::factory()->create(['reputation' => 0]);
    $program = testProgram(1.0);

    $response = $this->actingAs($user)->postJson('/api/v1/reports', [
        'title' => 'Critical Remote Code Execution',
        'severity' => 'Critical',
        'description' => 'I can access the root terminal of the server via a header injection.',
        'program_id' => $program->id
    ]);

    $response->assertStatus(201);

    // Refresh the user from the DB and check the math
    expect($user->fresh()->reputation)->toBe(100);
});

it('multiplies points correctly for high-value programs', function () {
    $user = User::factory()->create(['reputation' => 0]);
    $program = testProgram(2.5);

    $this->actingAs($user)->postJson('/api/v1/reports', [
        'title' => 'High Severity Data Leak',
        'severity' => 'High', // High = 50 base points
        'description' => 'User private emails are leaking through the profile API endpoint.',
        'program_id' => $program->id
    ]);

    // 50 base points * 2.5 multiplier = 125 points
    expect($user->fresh()->reputation)->toBe(125);
});
