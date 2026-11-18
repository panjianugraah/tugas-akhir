<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    private array $messages = [
        'required'           => ':attribute wajib diisi.',
        'tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
        'nama_kelas.unique'  => 'Kelas ini sudah ada di tahun ajaran tersebut.',
        'exists'             => ':attribute tidak ditemukan.',
    ];

    public function index(Request $request)
    {
        $kelas = Kelas::with('waliKelas:id,nama_lengkap')
            ->withCount('siswa')
            ->when($request->tahun_ajaran, fn($q, $v) => $q->where('tahun_ajaran', $v))
            ->when($request->tingkat, fn($q, $v) => $q->where('tingkat', $v))
            ->when($request->search, fn($q, $s) => $q->where('nama_kelas', 'like', "%{$s}%"))
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get();

        return response()->json(['success' => true, 'total' => $kelas->count(), 'data' => $kelas]);
    }

    public function show(Kelas $kelas)
    {
        return response()->json([
            'success' => true,
            'data'    => $kelas->load(['waliKelas', 'siswa' => fn($q) => $q->orderBy('nama_lengkap')]),
        ]);
    }

    public function store(Request $request)
    {
        $kelas = Kelas::create($request->validate($this->rules($request), $this->messages));

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan.',
            'data' => $kelas,
        ], 201);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $kelas->update($request->validate($this->rules($request, $kelas), $this->messages));

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil diupdate.',
            'data' => $kelas->fresh('waliKelas'),
        ]);
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete(); // siswa di kelas ini otomatis kelas_id = null
        return response()->json(['success' => true, 'message' => 'Kelas berhasil dihapus.']);
    }

    private function rules(Request $request, ?Kelas $kelas = null): array
    {
        $req = $kelas ? 'sometimes' : 'required';
        $tahun = $request->tahun_ajaran ?? $kelas?->tahun_ajaran;

        return [
            'nama_kelas'    => [
                $req,
                'string',
                'max:50',
                Rule::unique('kelas')->where('tahun_ajaran', $tahun)->ignore($kelas?->id)
            ],
            'tingkat'       => "$req|integer|between:1,12",
            'tahun_ajaran'  => [$req, 'regex:/^\d{4}\/\d{4}$/'],
            'kapasitas'     => 'sometimes|integer|min:1',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ];
    }
}
