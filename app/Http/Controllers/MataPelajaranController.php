<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $data = MataPelajaran::query()
            ->withCount(['materi', 'guru'])
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:mata_pelajaran,nama',
        ]);

        $mapel = MataPelajaran::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Mata pelajaran berhasil ditambahkan.',
            'data' => $mapel,
        ], 201);
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mata_pelajaran', 'nama')
                    ->ignore($mataPelajaran->id),
            ],
        ]);

        $mataPelajaran->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Mata pelajaran berhasil diperbarui.',
            'data' => $mataPelajaran,
        ]);
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        if (
            $mataPelajaran->materi()->exists() ||
            $mataPelajaran->guru()->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Mata pelajaran masih digunakan oleh guru atau materi repository.',
            ], 422);
        }

        $mataPelajaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mata pelajaran berhasil dihapus.',
        ]);
    }
}
