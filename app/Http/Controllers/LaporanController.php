<?php

namespace App\Http\Controllers;

use App\Models\DataPpdb;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PeriodePpdb;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Laporan PPDB wajib memilih periode
     * agar data tahun/gelombang lain tidak tercampur.
     */
    public function ppdb(Request $request)
    {
        $filter = $request->validate([
            'periode_ppdb_id' => 'required|integer|exists:periode_ppdb,id',
            'status' => 'nullable|in:menunggu,diterima,ditolak',
        ]);

        $periode = PeriodePpdb::findOrFail(
            $filter['periode_ppdb_id']
        );

        $pendaftar = DataPpdb::query()
            ->where('periode_ppdb_id', $periode->id)
            ->when($filter['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $baris = $pendaftar->map(function ($item) {
            return [
                $item->nisn,
                $item->nama_lengkap,
                $item->jenis_kelamin,
                $item->asal_sekolah,
                ucfirst($item->status),
            ];
        })->all();

        return $this->buatPdf(
            judul: 'Laporan Pendaftar PPDB',
            keterangan: [
                'Periode' => $periode->nama_periode,
                'Status' => $filter['status'] ?? 'Semua status',
                'Kuota penerimaan' => $periode->kuota ?? 'Belum ditentukan',
            ],
            kolom: [
                'NISN',
                'Nama Lengkap',
                'L/P',
                'Asal Sekolah',
                'Status',
            ],
            baris: $baris,
            namaFile: 'laporan-ppdb'
        );
    }

    public function siswa(Request $request)
    {
        $filter = $request->validate([
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'status' => 'nullable|in:aktif,lulus,pindah,keluar',
        ]);

        $kelas = ! empty($filter['kelas_id'])
            ? Kelas::findOrFail($filter['kelas_id'])
            : null;

        $siswa = Siswa::query()
            ->with('kelas')
            ->when($filter['kelas_id'] ?? null, function ($query, $id) {
                $query->where('kelas_id', $id);
            })
            ->when($filter['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $baris = $siswa->map(function ($item) {
            return [
                $item->nis,
                $item->nisn,
                $item->nama_lengkap,
                $item->jenis_kelamin,
                $item->kelas?->nama_kelas ?? '-',
                $item->kelas?->tahun_ajaran ?? '-',
                ucfirst($item->status),
            ];
        })->all();

        return $this->buatPdf(
            judul: 'Laporan Data Siswa',
            keterangan: [
                'Kelas' => $kelas
                    ? "{$kelas->nama_kelas} ({$kelas->tahun_ajaran})"
                    : 'Semua kelas',
                'Status' => $filter['status'] ?? 'Semua status',
            ],
            kolom: [
                'NIS',
                'NISN',
                'Nama Lengkap',
                'L/P',
                'Kelas',
                'Tahun Ajaran Kelas',
                'Status',
            ],
            baris: $baris,
            namaFile: 'laporan-siswa'
        );
    }

    public function guru(Request $request)
    {
        $filter = $request->validate([
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $guru = Guru::query()
            ->when($filter['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $baris = $guru->map(function ($item) {
            return [
                $item->nip ?? '-',
                $item->nama_lengkap,
                $item->jenis_kelamin,
                $item->mata_pelajaran ?? '-',
                ucfirst($item->status),
            ];
        })->all();

        return $this->buatPdf(
            judul: 'Laporan Data Guru',
            keterangan: [
                'Status' => $filter['status'] ?? 'Semua status',
            ],
            kolom: [
                'NIP',
                'Nama Lengkap',
                'L/P',
                'Mata Pelajaran',
                'Status',
            ],
            baris: $baris,
            namaFile: 'laporan-guru'
        );
    }

    private function buatPdf(
        string $judul,
        array $keterangan,
        array $kolom,
        array $baris,
        string $namaFile
    ) {
        $pdf = Pdf::loadView('tabel', [
            'judul' => $judul,
            'keterangan' => $keterangan,
            'kolom' => $kolom,
            'baris' => $baris,
            'jumlah' => count($baris),
            'dicetakPada' => now()->format('d-m-Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            $namaFile . '-' . now()->format('Ymd-His') . '.pdf'
        );
    }
}