<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    private array $messages = [
        'required'   => ':attribute wajib diisi.',
        'nip.digits' => 'NIP harus 18 digit angka.',
        'unique'     => ':attribute sudah terdaftar.',
        'email.email' => 'Format email tidak valid.',
    ];

    public function index(Request $request)
    {
        $guru = Guru::query()
            ->with('mapel:id,nama')
            ->when(
                $request->status,
                fn($q, $status) => $q->where('status', $status)
            )
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhereHas('mapel', function ($mapel) use ($search) {
                            $mapel->where('nama', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total' => $guru->count(),
            'data' => $guru,
        ]);
    }

    public function show(Guru $guru)
    {
        return response()->json([
            'success' => true,
            'data' => $guru->load([
                'kelasWali',
                'mapel:id,nama',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $guru = Guru::create($request->validate($this->rules(), $this->messages));


        return response()->json([
            'success' => true,
            'message' => 'Guru berhasil ditambahkan.',
            'data' => $guru->load('mapel:id,nama'),
        ], 201);
    }

    public function update(Request $request, Guru $guru)
    {
        $guru->update($request->validate($this->rules($guru->id, true), $this->messages));

        return response()->json(['success' => true, 'message' => 'Guru berhasil diupdate.', 'data' => $guru->load('mapel:id,nama'),]);
    }

    public function destroy(Guru $guru)
    {
        $guru->delete(); // kelas yang diwalikan otomatis wali_kelas_id = null
        return response()->json(['success' => true, 'message' => 'Guru berhasil dihapus.']);
    }

    private function rules(
        ?int $id = null,
        bool $isUpdate = false
    ): array {
        $req = $isUpdate ? 'sometimes|required' : 'required';

        return [
            'nip' => [
                'nullable',
                'digits:18',
                Rule::unique('guru', 'nip')->ignore($id),
            ],
            'nama_lengkap' => "$req|string|max:255",
            'jenis_kelamin' => "$req|in:L,P",
            'tempat_lahir' => "$req|string|max:100",
            'tanggal_lahir' => "$req|date",
            'no_hp' => "$req|string|max:20",
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('guru', 'email')->ignore($id),
            ],
            'alamat' => "$req|string",
            'status' => 'sometimes|required|in:aktif,nonaktif',
            'mata_pelajaran_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'required',
                'integer',
                'exists:mata_pelajaran,id',
            ],
        ];
    }
}
