<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\DataPpdb;
use App\Models\PeriodePpdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PpdbController extends Controller
{
    // GET /api/ppdb  → daftar semua pendaftar (bisa difilter)
    public function list(Request $request)
    {
        $ppdb = DataPpdb::query()
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => $q->where(function ($qq) use ($search) {
                $qq->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            }))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total'   => $ppdb->count(),
            'data'    => $ppdb,
        ]);
    }

    // GET /api/ppdb/{id}  → detail satu pendaftar
    public function show(DataPpdb $ppdb)
    {
        return response()->json([
            'success' => true,
            'data'    => $ppdb,
        ]);
    }

    // POST /api/ppdb  dan  POST /formppdb
    public function store(Request $request)
    {
        $data = $request->validate([
            'periode_ppdb_id' =>
            'required|integer|exists:periode_ppdb,id',

            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|digits:10|unique:data_ppdb,nisn',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'asal_sekolah' => 'required|string|max:255',
            'nama_orang_tua' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string',
        ], [
            'required' => ':attribute wajib diisi.',
            'periode_ppdb_id.exists' => 'Periode PPDB tidak ditemukan.',
            'nisn.digits' => 'NISN harus 10 digit angka.',
            'nisn.unique' => 'NISN ini sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $sekarang = now();

        $periode = PeriodePpdb::query()
            ->whereKey($data['periode_ppdb_id'])
            ->where('tanggal_mulai', '<=', $sekarang)
            ->where('tanggal_selesai', '>=', $sekarang)
            ->first();

        if (! $periode) {
            throw ValidationException::withMessages([
                'periode_ppdb_id' =>
                'Periode pendaftaran belum dibuka atau sudah ditutup.',
            ]);
        }

        // Status awal ditentukan server, bukan dari form publik.
        $data['status'] = 'menunggu';

        // Kuota tidak diperiksa karena hanya sebagai informasi.
        $ppdb = DataPpdb::create($data);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil dikirim.',
                'data' => $ppdb,
            ], 201);
        }

        return back()->with('success', 'Pendaftaran berhasil dikirim.');
    }

    public function update(Request $request, DataPpdb $ppdb)
    {
        $validated = $request->validate([
            'status' => 'required|in:menunggu,diterima,ditolak',
        ]);

        $hasil = DB::transaction(function () use ($ppdb, $validated) {
            // Kunci pendaftar selama proses agar penerimaan bersamaan
            // tidak membuat siswa dua kali.
            $pendaftar = DataPpdb::query()
                ->whereKey($ppdb->id)
                ->lockForUpdate()
                ->firstOrFail();

            $siswa = Siswa::query()
                ->where('ppdb_id', $pendaftar->id)
                ->first();

            // Jangan membatalkan penerimaan begitu saja jika siswa
            // sudah terbentuk. Pembatalan perlu proses tersendiri.
            if ($siswa && $validated['status'] !== 'diterima') {
                throw ValidationException::withMessages([
                    'status' => [
                        'Pendaftar sudah menjadi siswa. Pembatalan penerimaan harus melalui proses tersendiri.',
                    ],
                ]);
            }

            if ($validated['status'] === 'diterima' && ! $siswa) {
                // Jangan menghubungkan otomatis ke siswa lain
                // hanya karena NISN-nya sama.
                if (
                    Siswa::where('nisn', $pendaftar->nisn)->exists()
                ) {
                    throw ValidationException::withMessages([
                        'status' => [
                            'NISN pendaftar sudah digunakan oleh data siswa lain. Periksa data siswa terlebih dahulu.',
                        ],
                    ]);
                }

                // Contoh NIS otomatis. Sesuaikan jika sekolah
                // mempunyai aturan penomoran NIS sendiri.
                $idPendaftar = (int) $pendaftar->id;

                if ($idPendaftar < 1 || $idPendaftar > 999999) {
                    throw ValidationException::withMessages([
                        'status' => [
                            'ID pendaftar sudah di luar kapasitas NIS enam digit.',
                        ],
                    ]);
                }

                $tahun = now()->format('y');

                $nis = $tahun . str_pad(
                    (string) $idPendaftar,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

                if (Siswa::where('nis', $nis)->exists()) {
                    throw ValidationException::withMessages([
                        'status' => [
                            'NIS {$nis} sudah digunakan. Periksa aturan penomoran NIS.',
                        ],
                    ]);
                }

                $siswa = Siswa::create([
                    'ppdb_id' => $pendaftar->id,
                    'kelas_id' => null,
                    'nis' => $nis,
                    'nisn' => $pendaftar->nisn,
                    'nama_lengkap' => $pendaftar->nama_lengkap,
                    'jenis_kelamin' => $pendaftar->jenis_kelamin,
                    'tempat_lahir' => $pendaftar->tempat_lahir,
                    'tanggal_lahir' => $pendaftar->tanggal_lahir,
                    'nama_orang_tua' => $pendaftar->nama_orang_tua,
                    'no_hp' => $pendaftar->no_hp,
                    'email' => $pendaftar->email,
                    'alamat' => $pendaftar->alamat,
                    'status' => 'aktif',
                ]);
            }

            $pendaftar->update([
                'status' => $validated['status'],
            ]);

            return [
                'pendaftar' => $pendaftar,
                'siswa' => $siswa,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => $validated['status'] === 'diterima'
                ? 'Pendaftar diterima dan data siswa sudah tersedia.'
                : 'Status pendaftar berhasil diperbarui.',
            'data' => $hasil['pendaftar'],
            'siswa' => $hasil['siswa'],
        ]);
    }

    public function destroy($id)
    {
        $ppdb = DataPpdb::findOrFail($id);
        $ppdb->delete();

        return response()->json([
            'message' => 'Data ppdb berhasil dihapus',
        ]);
    }
}
