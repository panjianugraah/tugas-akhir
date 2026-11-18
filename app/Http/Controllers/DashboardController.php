<?php

namespace App\Http\Controllers;

use App\Models\DataPpdb;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PeriodePpdb;
use App\Models\Siswa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'periode_ppdb_id' => 'nullable|integer|exists:periode_ppdb,id',
        ]);

        $periode = PeriodePpdb::query()
            ->orderByDesc('tanggal_mulai')
            ->get(['id', 'nama_periode']);

        // Default: periode dengan tanggal mulai paling baru.
        // Pengguna bisa menggantinya melalui dropdown.
        $periodeId = ! empty($filter['periode_ppdb_id'])
            ? (int) $filter['periode_ppdb_id']
            : $periode->first()?->id;

        $statusCounts = collect();
        $pendaftarTerbaru = collect();

        if ($periodeId) {
            $statusCounts = DataPpdb::query()
                ->where('periode_ppdb_id', $periodeId)
                ->selectRaw('status, COUNT(*) as jumlah')
                ->groupBy('status')
                ->pluck('jumlah', 'status');

            $pendaftarTerbaru = DataPpdb::query()
                ->where('periode_ppdb_id', $periodeId)
                ->latest()
                ->limit(5)
                ->get([
                    'id',
                    'nama_lengkap',
                    'asal_sekolah',
                    'status',
                ]);
        }

        $grafikStatus = collect([
            ['key' => 'menunggu', 'label' => 'Menunggu'],
            ['key' => 'diterima', 'label' => 'Diterima'],
            ['key' => 'ditolak', 'label' => 'Ditolak'],
        ])->map(function ($status) use ($statusCounts) {
            return [
                ...$status,
                'jumlah' => (int) $statusCounts->get($status['key'], 0),
            ];
        })->values();

        $grafikKelas = Kelas::query()
            ->withCount([
                'siswa as siswa_aktif_count' => function ($query) {
                    $query->where('status', 'aktif');
                },
            ])
            ->orderByDesc('siswa_aktif_count')
            ->orderBy('nama_kelas')
            ->orderBy('id')
            ->limit(6)
            ->get([
                'id',
                'nama_kelas',
                'tahun_ajaran',
            ])
            ->map(function ($kelas) {
                return [
                    'id' => $kelas->id,
                    'label' => $kelas->nama_kelas
                        . ' (' . $kelas->tahun_ajaran . ')',
                    'jumlah' => (int) $kelas->siswa_aktif_count,
                ];
            });

        return view('pages.admin.dashboard', [
            'ringkasan' => [
                'siswa_aktif' => Siswa::where('status', 'aktif')->count(),
                'guru_aktif' => Guru::where('status', 'aktif')->count(),
                'total_kelas' => Kelas::count(),
                'pendaftar_ppdb' => (int) $statusCounts->sum(),
            ],

            'periode' => $periode,
            'periodeTerpilih' => $periodeId,
            'grafikStatus' => $grafikStatus,
            'grafikKelas' => $grafikKelas,
            'pendaftarTerbaru' => $pendaftarTerbaru,

            'siswaTanpaKelas' => Siswa::query()
                ->where('status', 'aktif')
                ->whereNull('kelas_id')
                ->count(),
        ]);
    }
}
