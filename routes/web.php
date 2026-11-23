<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PeriodePpdbController;
use App\Http\Controllers\PpdbController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\SiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Batasan parameter
|--------------------------------------------------------------------------
| Mencegah teks seperti "kode-akses" dianggap sebagai ID repository.
*/

Route::pattern('repository', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'welcome')
    ->name('home');

Route::inertia('/ppdb', 'ppdb')
    ->name('ppdb');

Route::inertia('/ppdb/formppdb', 'formppdb')
    ->name('formppdb');

Route::inertia('/pengumuman', 'pengumuman-umum')
    ->name('pengumuman.publik.index');

Route::get('/pengumuman/{slug}', function (string $slug) {
    return Inertia::render('pengumuman-umum-detail', [
        'slug' => $slug,
    ]);
})->name('pengumuman.publik.show');

Route::inertia('/galeri', 'galeri-umum')
    ->name('galeri.publik');

Route::inertia('/repository', 'repository-umum')
    ->name('repository.publik');

/*
|--------------------------------------------------------------------------
| Endpoint JSON Publik
|--------------------------------------------------------------------------
| Route dalam web.php menggunakan session dan perlindungan CSRF.
*/

Route::prefix('api')->group(function () {
    // Periode aktif dan mendatang untuk jadwal PPDB publik.
    Route::get('periode-ppdb/jadwal', [
        PeriodePpdbController::class,
        'jadwalPublik',
    ]);

    // Periode yang sedang membuka pendaftaran.
    Route::get('periode-ppdb/aktif', [
        PeriodePpdbController::class,
        'aktif',
    ]);

    // Galeri dapat dilihat tanpa login.
    Route::get('galeri', [
        GaleriController::class,
        'index',
    ]);

    Route::get('galeri/{galeri}', [
        GaleriController::class,
        'show',
    ]);
});

/*
|--------------------------------------------------------------------------
| Repository Publik dengan Kode Akses
|--------------------------------------------------------------------------
| Controller wajib memeriksa akses pada publik() dan download().
*/

Route::prefix('api/repository')->group(function () {
    Route::get('publik', [
        RepositoryController::class,
        'publik',
    ]);

    Route::post('akses', [
        RepositoryController::class,
        'bukaAkses',
    ])->middleware('throttle:5,1');

    Route::post('keluar', [
        RepositoryController::class,
        'tutupAkses',
    ]);

    Route::get('{repository}/download', [
        RepositoryController::class,
        'download',
    ])->name('repository.download');
});

/*
|--------------------------------------------------------------------------
| Pengalihan Dashboard Setelah Login
|--------------------------------------------------------------------------
| Nama route "dashboard" dipertahankan agar dashboard() tetap bisa digunakan.
*/

Route::get('/dashboard', function (Request $request) {
    return redirect(
        $request->user()->can('view-monitoring')
            ? '/admin/dashboard'
            : '/admin/konten/repository'
    );
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Repository untuk Pengguna Login
|--------------------------------------------------------------------------
| Admin dan guru boleh mengakses halaman serta CRUD repository.
*/

Route::middleware('auth')->group(function () {
    Route::inertia(
        '/admin/konten/repository',
        'admin/repository'
    )->name('admin.konten.repository');

    Route::redirect(
        '/admin/repository',
        '/admin/konten/repository'
    )->name('admin.repository');

    // Guru membutuhkan daftar mapel untuk dropdown pada form repository.
    Route::get('/api/mata-pelajaran', [
        MataPelajaranController::class,
        'index',
    ])->name('mata-pelajaran.index');
});

/*
|--------------------------------------------------------------------------
| Pembuatan Akun: Khusus Admin yang Memiliki Izin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'can:manage-users'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('pengguna/tambah', [
            PenggunaController::class,
            'create',
        ])->name('pengguna.create');

        Route::post('pengguna', [
            PenggunaController::class,
            'store',
        ])
            ->middleware('throttle:10,1')
            ->name('pengguna.store');
    });

/*
|--------------------------------------------------------------------------
| Halaman Monitoring: Admin dan Kepala Sekolah
|--------------------------------------------------------------------------
| Kepala sekolah hanya dapat melihat. Seluruh perubahan data tetap
| dijaga oleh endpoint JSON khusus admin di bawah.
*/

Route::middleware(['auth', 'verified', 'can:view-monitoring'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');

        // Laporan.
        Route::inertia('laporan', 'admin/laporan')
            ->name('laporan');

        Route::get('laporan/ppdb/pdf', [
            LaporanController::class,
            'ppdb',
        ])->name('laporan.ppdb.pdf');

        Route::get('laporan/siswa/pdf', [
            LaporanController::class,
            'siswa',
        ])->name('laporan.siswa.pdf');

        Route::get('laporan/guru/pdf', [
            LaporanController::class,
            'guru',
        ])->name('laporan.guru.pdf');

        // Data akademik.
        Route::inertia('siswa', 'admin/siswa')
            ->name('siswa');

        Route::inertia('guru', 'admin/guru')
            ->name('guru');

        Route::inertia('kelas', 'admin/kelas')
            ->name('kelas');

        Route::inertia('mata-pelajaran', 'admin/mata-pelajaran')
            ->name('mata-pelajaran');

        // PPDB.
        Route::inertia('ppdbadmin', 'admin/ppdbadmin')
            ->name('ppdb');

        Route::inertia('periode-ppdb', 'admin/periode-ppdb')
            ->name('periode-ppdb');
    });

/*
|--------------------------------------------------------------------------
| Halaman Pengelolaan Konten: Khusus Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Konten selain repository.
        Route::prefix('konten')->name('konten.')->group(function () {
            Route::inertia('pengumuman', 'admin/pengumuman')
                ->name('pengumuman');

            Route::inertia('galeri', 'admin/galeri')
                ->name('galeri');
        });

        Route::redirect('galeri', '/admin/konten/galeri')
            ->name('galeri');
    });

/*
|--------------------------------------------------------------------------
| Endpoint JSON Baca-Saja: Admin dan Kepala Sekolah
|--------------------------------------------------------------------------
| Dipakai halaman monitoring dan laporan. Tidak ada endpoint tulis di sini.
*/

Route::middleware(['auth', 'can:view-monitoring'])
    ->prefix('api')
    ->group(function () {
        Route::apiResource('siswa', SiswaController::class)
            ->parameters(['siswa' => 'siswa'])
            ->only(['index', 'show']);

        Route::apiResource('guru', GuruController::class)
            ->parameters(['guru' => 'guru'])
            ->only(['index', 'show']);

        Route::apiResource('kelas', KelasController::class)
            ->parameters(['kelas' => 'kelas'])
            ->only(['index', 'show']);

        Route::get('ppdb', [
            PpdbController::class,
            'list',
        ]);

        Route::get('ppdb/{ppdb}', [
            PpdbController::class,
            'show',
        ]);

        Route::apiResource(
            'periode-ppdb',
            PeriodePpdbController::class
        )
            ->parameters(['periode-ppdb' => 'periodePpdb'])
            ->only(['index', 'show']);
    });

/*
|--------------------------------------------------------------------------
| Endpoint JSON Khusus Admin
|--------------------------------------------------------------------------
| Menjaga keamanan CRUD, bukan hanya menyembunyikan menu sidebar.
*/

Route::middleware(['auth', 'can:access-admin'])
    ->prefix('api')
    ->group(function () {
        // Akademik.
        Route::apiResource('siswa', SiswaController::class)
            ->parameters(['siswa' => 'siswa'])
            ->except(['index', 'show']);

        Route::apiResource('guru', GuruController::class)
            ->parameters(['guru' => 'guru'])
            ->except(['index', 'show']);

        Route::apiResource('kelas', KelasController::class)
            ->parameters(['kelas' => 'kelas'])
            ->except(['index', 'show']);

        // Index mapel sudah tersedia untuk semua akun login.
        // Perubahannya hanya boleh dilakukan admin.
        Route::apiResource(
            'mata-pelajaran',
            MataPelajaranController::class
        )
            ->parameters(['mata-pelajaran' => 'mataPelajaran'])
            ->only(['store', 'update', 'destroy']);

        // Pengelolaan PPDB, bukan pendaftaran publik.
        Route::put('ppdb/{ppdb}', [
            PpdbController::class,
            'update',
        ]);

        Route::patch('ppdb/{ppdb}', [
            PpdbController::class,
            'update',
        ]);

        Route::delete('ppdb/{ppdb}', [
            PpdbController::class,
            'destroy',
        ]);

        Route::post('ppdb/{ppdb}/terima', [
            PpdbController::class,
            'terima',
        ]);

        Route::apiResource(
            'periode-ppdb',
            PeriodePpdbController::class
        )
            ->parameters(['periode-ppdb' => 'periodePpdb'])
            ->except(['index', 'show']);

        // Pengumuman.
        Route::apiResource(
            'pengumuman',
            PengumumanController::class
        )->parameters(['pengumuman' => 'pengumuman']);

        // Galeri: pembacaan sudah tersedia secara publik.
        Route::post('galeri', [
            GaleriController::class,
            'store',
        ]);

        Route::match(['put', 'patch'], 'galeri/{galeri}', [
            GaleriController::class,
            'update',
        ]);

        Route::delete('galeri/{galeri}', [
            GaleriController::class,
            'destroy',
        ]);

        // Hanya admin yang boleh mengganti kode akses repository.
        Route::put('repository/kode-akses', [
            RepositoryController::class,
            'simpanKode',
        ]);

        Route::post('repository/reset-akses', [
            RepositoryController::class,
            'resetAkses',
        ]);
    });

/*
|--------------------------------------------------------------------------
| Endpoint CRUD Repository untuk Admin dan Guru
|--------------------------------------------------------------------------
| Diletakkan setelah route khusus repository.
*/

Route::middleware('auth')->prefix('api')->group(function () {
    Route::apiResource('repository', RepositoryController::class)
        ->parameters(['repository' => 'repository']);
});

require __DIR__ . '/settings.php';
