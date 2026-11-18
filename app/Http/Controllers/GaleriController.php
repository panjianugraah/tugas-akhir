<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    // GET /api/galeri
    public function index(Request $request)
    {
        $galeri = Galeri::query()
            ->when($request->search, function ($query, $search) {
                $query->where('deskripsi', 'like', "%{$search}%");
            })
            ->with('pembuat:id,name')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total' => $galeri->count(),
            'data' => $galeri,
        ]);
    }

    // GET /api/galeri/{galeri}
    public function show(Galeri $galeri)
    {
        return response()->json([
            'success' => true,
            'data' => $galeri,
        ]);
    }

    // POST /api/galeri
    public function store(Request $request)
    {
        $data = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $path = $request->file('gambar')->store('galeri', 'public');

        try {
            $galeri = Galeri::create([
                'created_by' => $request->user()->id,
                'gambar' => $path,
                'deskripsi' => $data['deskripsi'] ?? null,
            ]);
        } catch (\Throwable $error) {
            Storage::disk('public')->delete($path);
            throw $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto galeri berhasil ditambahkan.',
            'data' => $galeri,
        ], 201);
    }

    // POST /api/galeri/{galeri} dengan _method=PUT
    public function update(Request $request, Galeri $galeri)
    {
        $data = $request->validate(
            $this->rules(true),
            $this->messages()
        );

        // Jangan mengubah gambar jika tidak ada file pengganti.
        unset($data['gambar']);

        $pathLama = $galeri->gambar;
        $pathBaru = null;

        if ($request->hasFile('gambar')) {
            $pathBaru = $request->file('gambar')->store('galeri', 'public');
            $data['gambar'] = $pathBaru;
        }

        try {
            $galeri->update($data);
        } catch (\Throwable $error) {
            if ($pathBaru) {
                Storage::disk('public')->delete($pathBaru);
            }

            throw $error;
        }

        // Hapus gambar lama setelah perubahan berhasil disimpan.
        if (
            $pathBaru &&
            is_string($pathLama) &&
            trim($pathLama) !== '' &&
            $pathLama !== $pathBaru
        ) {
            Storage::disk('public')->delete($pathLama);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto galeri berhasil diperbarui.',
            'data' => $galeri->fresh(),
        ]);
    }

    // DELETE /api/galeri/{galeri}
    public function destroy(Galeri $galeri)
    {
        $path = $galeri->gambar;

        if (is_string($path) && trim($path) !== '') {
            Storage::disk('public')->delete($path);
        }

        $galeri->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto galeri berhasil dihapus.',
        ]);
    }

    private function rules(bool $update = false): array
    {
        return [
            'gambar' => [
                $update ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'deskripsi' => 'nullable|string|max:255',
        ];
    }

    private function messages(): array
    {
        return [
            'gambar.required' => 'Gambar wajib dipilih.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'deskripsi.max' => 'Deskripsi maksimal 255 karakter.',
        ];
    }
}