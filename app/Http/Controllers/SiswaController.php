<?php
namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private array $messages = [
        'required'    => ':attribute wajib diisi.',
        'nisn.digits' => 'NISN harus 10 digit angka.',
        'unique'      => ':attribute sudah terdaftar.',
        'exists'      => ':attribute tidak ditemukan.',
        'email.email' => 'Format email tidak valid.',
    ];

    // GET /api/siswa?status=&kelas_id=&search=
    public function index(Request $request)
    {
        $siswa = Siswa::with('kelas')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->kelas_id, fn ($q, $v) => $q->where('kelas_id', $v))
            ->when($request->search, fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('nama_lengkap', 'like', "%{$s}%")
                   ->orWhere('nis', 'like', "%{$s}%")
                   ->orWhere('nisn', 'like', "%{$s}%");
            }))
            ->latest()
            ->get();

        return response()->json(['success' => true, 'total' => $siswa->count(), 'data' => $siswa]);
    }

    // GET /api/siswa/{siswa}
    public function show(Siswa $siswa)
    {
        return response()->json(['success' => true, 'data' => $siswa->load('kelas.waliKelas')]);
    }

    // POST /api/siswa
    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages);
        $siswa = Siswa::create($data);

        return response()->json([
            'success' => true, 'message' => 'Siswa berhasil ditambahkan.', 'data' => $siswa,
        ], 201);
    }

    // PUT/PATCH /api/siswa/{siswa}
    public function update(Request $request, Siswa $siswa)
    {
        $data = $request->validate($this->rules($siswa->id, true), $this->messages);
        $siswa->update($data);

        return response()->json([
            'success' => true, 'message' => 'Siswa berhasil diupdate.', 'data' => $siswa->fresh('kelas'),
        ]);
    }

    // DELETE /api/siswa/{siswa}
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return response()->json(['success' => true, 'message' => 'Siswa berhasil dihapus.']);
    }

    private function rules(?int $id = null, bool $isUpdate = false): array
    {
        $req = $isUpdate ? 'sometimes' : 'required';

        return [
            'kelas_id'       => 'nullable|exists:kelas,id',
            'nis'            => [$req, 'string', 'max:20', Rule::unique('siswa', 'nis')->ignore($id)],
            'nisn'           => [$req, 'digits:10', Rule::unique('siswa', 'nisn')->ignore($id)],
            'nama_lengkap'   => "$req|string|max:255",
            'jenis_kelamin'  => "$req|in:L,P",
            'tempat_lahir'   => "$req|string|max:100",
            'tanggal_lahir'  => "$req|date",
            'nama_orang_tua' => "$req|string|max:255",
            'no_hp'          => "$req|string|max:20",
            'email'          => 'nullable|email',
            'alamat'         => "$req|string",
            'status'         => 'sometimes|in:aktif,lulus,pindah,keluar',
        ];
    }
}