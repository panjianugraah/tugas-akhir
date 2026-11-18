<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    private array $messages = [
        'required'     => ':attribute wajib diisi.',
        'gambar.image' => 'File harus berupa gambar.',
        'gambar.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
        'gambar.max'   => 'Ukuran gambar maksimal 2 MB.',
        'target.in'    => 'Target harus Siswa, Guru, Orang Tua, atau Umum.',
    ];

    // GET /api/pengumuman?status=&target=&search=&aktif=1
    public function index(Request $request)
    {
        $pengumuman = Pengumuman::query()
            ->with('pembuat:id,name')
            ->when(
                $request->status,
                fn($q, $status) => $q->where('status', $status)
            )
            ->when(
                $request->search,
                fn($q, $search) => $q->where(
                    'judul',
                    'like',
                    "%{$search}%"
                )
            )
            ->when($request->boolean('aktif'), function ($query) {
                $query->where('status', 'terbit')
                    ->where(function ($q) {
                        $q->whereNull('berlaku_sampai')
                            ->orWhereDate(
                                'berlaku_sampai',
                                '>=',
                                today()
                            );
                    });
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total' => $pengumuman->count(),
            'data' => $pengumuman,
        ]);
    }

    // GET /api/pengumuman/{pengumuman}
    public function show(Pengumuman $pengumuman)
    {
        return response()->json(['success' => true, 'data' => $pengumuman]);
    }

    // GET /api/pengumuman/slug/{slug}  → untuk halaman baca berita
    public function showBySlug(string $slug)
    {
        $pengumuman = Pengumuman::query()
            ->where('slug', $slug)
            ->where('status', 'terbit')
            ->where(function ($query) {
                $query->whereNull('berlaku_sampai')
                    ->orWhereDate('berlaku_sampai', '>=', today());
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $pengumuman,
        ]);
    }

    // POST /api/pengumuman  (multipart/form-data)
    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public');
        }

        $data['created_by'] = $request->user()->id;

        $pengumuman = Pengumuman::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dibuat.',
            'data'    => $pengumuman,
        ], 201);
    }

    // POST /api/pengumuman/{id} + _method=PUT  (multipart/form-data)
    public function update(Request $request, Pengumuman $pengumuman)
    {
        $data = $request->validate($this->rules(true), $this->messages);

        // Ganti gambar
        if ($request->hasFile('gambar')) {
            if ($pengumuman->gambar) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('pengumuman', 'public');
        }
        // Hapus gambar tanpa mengganti
        elseif ($request->boolean('hapus_gambar') && $pengumuman->gambar) {
            Storage::disk('public')->delete($pengumuman->gambar);
            $data['gambar'] = null;
        }

        unset($data['hapus_gambar']);
        $pengumuman->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diupdate.',
            'data'    => $pengumuman,
        ]);
    }

    // DELETE /api/pengumuman/{pengumuman}
    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete(); // gambar ikut terhapus (lihat Model)

        return response()->json(['success' => true, 'message' => 'Pengumuman berhasil dihapus.']);
    }

    private function rules(bool $isUpdate = false): array
    {
        $req = $isUpdate ? 'sometimes|required' : 'required';

        return [
            'judul' => "$req|string|max:255",
            'isi' => "$req|string",
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'berlaku_sampai' => 'nullable|date',
            'status' => $isUpdate
                ? 'sometimes|required|in:draft,terbit'
                : 'required|in:draft,terbit',
            'hapus_gambar' => 'sometimes|boolean',
        ];
    }

    // GET /api/pengumuman/publik
    public function publik()
    {
        $pengumuman = Pengumuman::query()
            ->where('status', 'terbit')
            ->where(function ($query) {
                $query->whereNull('berlaku_sampai')
                    ->orWhereDate('berlaku_sampai', '>=', today());
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total' => $pengumuman->count(),
            'data' => $pengumuman,
        ]);
    }
}
