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

Route::view('/', 'pages.public.home')
    ->name('home');

Route::view('/ppdb', 'pages.public.ppdb')
    ->name('ppdb');

Route::view('/ppdb/formppdb', 'pages.public.formppdb')
    ->name('formppdb');

Route::view('/pengumuman', 'pages.public.pengumuman.index')
    ->name('pengumuman.publik.index');

Route::get('/pengumuman/{slug}', function (string $slug) {
    return view('pages.public.pengumuman.detail', ['slug' => $slug]);
})->name('pengumuman.publik.show');

Route::view('/galeri', 'pages.public.galeri')
    ->name('galeri.publik');

Route::view('/repository', 'pages.public.repository')
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
        $request->user()->can('access-admin')
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
    Route::view(
        '/admin/konten/repository',
        'pages.admin.repository'
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
| Halaman Dashboard dan Pengelolaan Khusus Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');

        // Laporan.
        Route::view('laporan', 'pages.admin.laporan')
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
        Route::view('siswa', 'pages.admin.siswa')
            ->name('siswa');

        Route::view('guru', 'pages.admin.guru')
            ->name('guru');

        Route::view('kelas', 'pages.admin.kelas')
            ->name('kelas');

        Route::view('mata-pelajaran', 'pages.admin.mata-pelajaran')
            ->name('mata-pelajaran');

        // PPDB.
        Route::view('ppdbadmin', 'pages.admin.ppdbadmin')
            ->name('ppdb');

        Route::view('periode-ppdb', 'pages.admin.periode-ppdb')
            ->name('periode-ppdb');

        // Konten selain repository.
        Route::prefix('konten')->name('konten.')->group(function () {
            Route::view('pengumuman', 'pages.admin.pengumuman')
                ->name('pengumuman');

            Route::view('galeri', 'pages.admin.galeri')
                ->name('galeri');
        });

        Route::redirect('galeri', '/admin/konten/galeri')
            ->name('galeri');
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
            ->parameters(['siswa' => 'siswa']);

        Route::apiResource('guru', GuruController::class)
            ->parameters(['guru' => 'guru']);

        Route::apiResource('kelas', KelasController::class)
            ->parameters(['kelas' => 'kelas']);

        // Index mapel sudah tersedia untuk semua akun login.
        // Perubahannya hanya boleh dilakukan admin.
        Route::apiResource(
            'mata-pelajaran',
            MataPelajaranController::class
        )
            ->parameters(['mata-pelajaran' => 'mataPelajaran'])
            ->only(['store', 'update', 'destroy']);

        // Pengelolaan PPDB, bukan pendaftaran publik.
        Route::get('ppdb', [
            PpdbController::class,
            'list',
        ]);

        Route::get('ppdb/{ppdb}', [
            PpdbController::class,
            'show',
        ]);

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
        )->parameters(['periode-ppdb' => 'periodePpdb']);

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

require __DIR__.'/settings.php';
