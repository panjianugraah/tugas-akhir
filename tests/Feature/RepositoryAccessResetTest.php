<?php

use App\Models\RepositorySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an admin to revoke old visitor access without changing the code', function () {
    $setting = RepositorySetting::create(['kode_akses' => 'kode-sekolah', 'versi' => 1]);
    $admin = User::factory()->create();
    config(['access.admin_user_ids' => [(string) $admin->id]]);
    $oldAccess = ['versi' => 1, 'expired_at' => now()->addHour()->timestamp];

    $this->withSession(['repository_access' => $oldAccess])
        ->getJson('/api/repository/publik')->assertOk();

    $this->actingAs($admin)->postJson('/api/repository/reset-akses')->assertOk();
    expect($setting->fresh()->versi)->toBe(2)
        ->and($setting->fresh()->kode_akses)->toBe('kode-sekolah');

    auth()->logout();
    $this->withSession(['repository_access' => $oldAccess])
        ->getJson('/api/repository/publik')->assertForbidden();

    $this->postJson('/api/repository/akses', ['kode' => 'kode-sekolah'])->assertOk();
    $this->getJson('/api/repository/publik')->assertOk();
});

it('does not allow guests or non-admin users to reset visitor access', function () {
    $setting = RepositorySetting::create(['kode_akses' => 'kode-sekolah', 'versi' => 1]);
    config(['access.admin_user_ids' => []]);

    $this->postJson('/api/repository/reset-akses')->assertUnauthorized();
    $this->actingAs(User::factory()->create())
        ->postJson('/api/repository/reset-akses')->assertForbidden();
    expect($setting->fresh()->versi)->toBe(1);
});

it('requires a repository code to be configured before resetting access', function () {
    $admin = User::factory()->create();
    config(['access.admin_user_ids' => [(string) $admin->id]]);

    $this->actingAs($admin)->postJson('/api/repository/reset-akses')
        ->assertUnprocessable();
    expect(RepositorySetting::count())->toBe(0);
});
