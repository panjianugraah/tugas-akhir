<?php

namespace App\Http\Controllers;

use App\Models\PeriodePpdb;
use Illuminate\Http\Request;

class PeriodePpdbController extends Controller
{
    /**
     * Daftar periode beserta jumlah pendaftar dan peserta diterima.
     *
     * GET /api/periode-ppdb
     * GET /api/periode-ppdb?search=2027
     */
    public function index(Request $request)
    {
        $periode = PeriodePpdb::query()
            ->withCount([
                'pendaftar',
                'pendaftar as diterima_count' => function ($query) {
                    $query->where('status', 'diterima');
                },
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(
                    'nama_periode',
                    'like',
                    "%{$search}%"
                );
            })
            ->orderByDesc('tanggal_mulai')
            ->get();

        return response()->json([
            'success' => true,
            'total' => $periode->count(),
            'data' => $periode,
        ]);
    }

    /**
     * Detail satu periode.
     *
     * GET /api/periode-ppdb/{periodePpdb}
     */
    public function show(PeriodePpdb $periodePpdb)
    {
        $periodePpdb->loadCount([
            'pendaftar',
            'pendaftar as diterima_count' => function ($query) {
                $query->where('status', 'diterima');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $periodePpdb,
        ]);
    }

    /**
     * Tambah periode.
     *
     * POST /api/periode-ppdb
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $data['tanggal_mulai'] = \Carbon\Carbon::parse($data['tanggal_mulai'])
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        $data['tanggal_selesai'] = \Carbon\Carbon::parse($data['tanggal_selesai'])
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        $periode = PeriodePpdb::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Periode PPDB berhasil ditambahkan.',
            'data' => $periode,
        ], 201);
    }

    /**
     * Edit periode.
     * Kirim nama, tanggal mulai, dan tanggal selesai saat update.
     *
     * PUT/PATCH /api/periode-ppdb/{periodePpdb}
     */
    public function update(
        Request $request,
        PeriodePpdb $periodePpdb
    ) {
        $data = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $data['tanggal_mulai'] = \Carbon\Carbon::parse($data['tanggal_mulai'])
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        $data['tanggal_selesai'] = \Carbon\Carbon::parse($data['tanggal_selesai'])
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        // Tidak membandingkan kuota dengan jumlah peserta.
        // Kuota boleh diubah meskipun jumlah diterima sudah melampauinya.
        $periodePpdb->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Periode PPDB berhasil diperbarui.',
            'data' => $periodePpdb->fresh(),
        ]);
    }

    /**
     * Hapus periode hanya jika belum memiliki pendaftar.
     *
     * DELETE /api/periode-ppdb/{periodePpdb}
     */
    public function destroy(PeriodePpdb $periodePpdb)
    {
        if ($periodePpdb->pendaftar()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak dapat dihapus karena sudah memiliki pendaftar.',
            ], 422);
        }

        $periodePpdb->delete();

        return response()->json([
            'success' => true,
            'message' => 'Periode PPDB berhasil dihapus.',
        ]);
    }

    private function rules(): array
    {
        return [
            'nama_periode' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'kuota' => 'nullable|integer|min:0',
        ];
    }

    private function messages(): array
    {
        return [
            'nama_periode.required' => 'Nama periode wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date' => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after' =>
            'Tanggal selesai harus setelah tanggal mulai.',
            'kuota.integer' => 'Kuota harus berupa bilangan bulat.',
            'kuota.min' => 'Kuota tidak boleh negatif.',
        ];
    }

    public function aktif()
    {
        $sekarang = now();

        $periode = PeriodePpdb::query()
            ->where('tanggal_mulai', '<=', $sekarang)
            ->where('tanggal_selesai', '>=', $sekarang)
            ->orderBy('tanggal_mulai')
            ->get([
                'id',
                'nama_periode',
                'tanggal_mulai',
                'tanggal_selesai',
            ]);

        return response()->json([
            'success' => true,
            'data' => $periode,
        ]);
    }

    /**
     * Periode yang sedang berjalan atau akan datang untuk jadwal publik.
     * GET /api/periode-ppdb/jadwal
     */
    public function jadwalPublik()
    {
        $periode = PeriodePpdb::query()
            ->where('tanggal_selesai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->get([
                'id',
                'nama_periode',
                'tanggal_mulai',
                'tanggal_selesai',
                'kuota',
            ]);

        return response()->json([
            'success' => true,
            'data' => $periode,
        ]);
    }
}
