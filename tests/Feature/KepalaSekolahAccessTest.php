<?php

use App\Models\User;
use Database\Seeders\KepalaSekolahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function kepalaSekolah(): User
{
    $user = User::factory()->create();
    config([
        'access.admin_user_ids' => [],
        'access.kepala_sekolah_user_ids' => [(string) $user->id],
    ]);

    return $user;
}

it('creates a verified kepala sekolah account from the seeder', function () {
    putenv('KEPALA_SEKOLAH_EMAIL=kepsek@sekolah.test');
    putenv('KEPALA_SEKOLAH_PASSWORD=RahasiaKepsek#2026');
    config(['access.admin_user_ids' => [], 'access.kepala_sekolah_user_ids' => []]);

    $this->seed(KepalaSekolahSeeder::class);

    $user = User::where('email', 'kepsek@sekolah.test')->firstOrFail();

    expect($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('RahasiaKepsek#2026', $user->password))->toBeTrue()
        ->and(config('access.kepala_sekolah_user_ids'))->toContain((string) $user->id)
        ->and($user->can('view-monitoring'))->toBeTrue()
        ->and($user->can('access-admin'))->toBeFalse();

    // Menjalankan ulang tidak membuat akun ganda.
    $this->seed(KepalaSekolahSeeder::class);
    expect(User::where('email', 'kepsek@sekolah.test')->count())->toBe(1);

    putenv('KEPALA_SEKOLAH_EMAIL');
    putenv('KEPALA_SEKOLAH_PASSWORD');
});

it('lets kepala sekolah open the monitoring pages', function (string $url) {
    $this->actingAs(kepalaSekolah())->get($url)->assertOk();
})->with([
    '/admin/dashboard',
    '/admin/laporan',
    '/admin/siswa',
    '/admin/guru',
    '/admin/kelas',
    '/admin/mata-pelajaran',
    '/admin/ppdbadmin',
    '/admin/periode-ppdb',
]);

it('redirects kepala sekolah to the dashboard after login', function () {
    $this->actingAs(kepalaSekolah())
        ->get('/dashboard')
        ->assertRedirect('/admin/dashboard');
});

it('lets kepala sekolah read monitoring data', function (string $url) {
    $this->actingAs(kepalaSekolah())->getJson($url)->assertOk();
})->with([
    '/api/siswa',
    '/api/guru',
    '/api/kelas',
    '/api/ppdb',
    '/api/periode-ppdb',
]);

it('blocks kepala sekolah from changing data', function (string $method, string $url) {
    // Record nyata dibuat agar yang diuji adalah izin, bukan 404 karena data tidak ada.
    $orang = [
        'jenis_kelamin' => 'L',
        'tempat_lahir' => 'Solo',
        'tanggal_lahir' => '2012-01-01',
        'no_hp' => '0800000000',
        'alamat' => 'Alamat',
        'created_at' => now(),
        'updated_at' => now(),
    ];
    DB::table('data_ppdb')->insert($orang + ['id' => 1, 'nama_lengkap' => 'Pendaftar',
        'nisn' => '1111111111', 'asal_sekolah' => 'SD', 'nama_orang_tua' => 'Ortu']);
    DB::table('siswa')->insert($orang + ['id' => 1, 'nis' => '001', 'nisn' => '2222222222',
        'nama_lengkap' => 'Siswa', 'nama_orang_tua' => 'Ortu']);
    DB::table('guru')->insert($orang + ['id' => 1, 'nama_lengkap' => 'Guru']);

    $this->actingAs(kepalaSekolah())->json($method, $url, [])->assertForbidden();

    expect(DB::table('data_ppdb')->value('status'))->toBe('menunggu')
        ->and(DB::table('siswa')->count())->toBe(1)
        ->and(DB::table('guru')->count())->toBe(1);
})->with([
    ['POST', '/api/siswa'],
    ['PUT', '/api/siswa/1'],
    ['DELETE', '/api/siswa/1'],
    ['POST', '/api/guru'],
    ['DELETE', '/api/guru/1'],
    ['POST', '/api/kelas'],
    ['POST', '/api/mata-pelajaran'],
    ['PUT', '/api/ppdb/1'],
    ['POST', '/api/ppdb/1/terima'],
    ['DELETE', '/api/ppdb/1'],
    ['POST', '/api/periode-ppdb'],
    ['POST', '/api/pengumuman'],
    ['POST', '/api/galeri'],
    ['PUT', '/api/repository/kode-akses'],
]);

it('keeps content management and account creation admin-only', function (string $url) {
    $this->actingAs(kepalaSekolah())->get($url)->assertForbidden();
})->with([
    '/admin/konten/pengumuman',
    '/admin/konten/galeri',
    '/admin/pengguna/tambah',
]);

it('still blocks regular guru accounts from monitoring', function (string $url) {
    $guru = User::factory()->create();
    config(['access.admin_user_ids' => [], 'access.kepala_sekolah_user_ids' => []]);

    $this->actingAs($guru)->get($url)->assertForbidden();
})->with([
    '/admin/dashboard',
    '/admin/laporan',
    '/api/siswa',
]);

it('keeps full access for admin', function () {
    $admin = User::factory()->create();
    config(['access.admin_user_ids' => [(string) $admin->id]]);

    $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    $this->actingAs($admin)->get('/admin/konten/pengumuman')->assertOk();
    $this->actingAs($admin)->getJson('/api/siswa')->assertOk();
    $this->actingAs($admin)->postJson('/api/kelas', [])->assertUnprocessable();
});
