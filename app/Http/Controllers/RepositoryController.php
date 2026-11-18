<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\RepositorySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepositoryController extends Controller
{
    public function index(Request $request)
    {
        $repository = Repository::query()
            ->with('mataPelajaran:id,nama', 'pembuat:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                        ->orWhere('nama_file', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total' => $repository->count(),
            'data' => $repository,
        ]);
    }

    public function show(Repository $repository)

    {
        return response()->json([
            'success' => true,
            'data' => $repository->load('mataPelajaran:id,nama'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $upload = $request->file('file');
        $path = $upload->store('dokumen', 'repository');

        try {
            $repository = Repository::create([
                // Tambahkan ID mata pelajaran.
                'created_by' => $request->user()->id,
                'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'path' => $path,
                'nama_file' => basename(
                    str_replace('\\', '/', $upload->getClientOriginalName())
                ),
                'ekstensi' => strtolower(
                    $upload->getClientOriginalExtension()
                ),
                'ukuran' => $upload->getSize(),
            ]);
        } catch (\Throwable $error) {
            Storage::disk('repository')->delete($path);
            throw $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil ditambahkan.',
            'data' => $repository->load('mataPelajaran:id,nama'),
        ], 201);
    }

    public function update(Request $request, Repository $repository)
    {
        $data = $request->validate(
            $this->rules(true),
            $this->messages()
        );

        // Input upload bukan kolom database.
        unset($data['file']);

        $disk = Storage::disk('repository');
        $pathLama = $repository->path;
        $pathBaru = null;

        if ($request->hasFile('file')) {
            $upload = $request->file('file');

            $pathBaru = $upload->store('dokumen', 'repository');

            $data['path'] = $pathBaru;
            $data['nama_file'] = basename(
                str_replace('\\', '/', $upload->getClientOriginalName())
            );
            $data['ekstensi'] = strtolower(
                $upload->getClientOriginalExtension()
            );
            $data['ukuran'] = $upload->getSize();
        }

        try {
            $repository->update($data);
        } catch (\Throwable $error) {
            // Jika database gagal diperbarui, buang file baru.
            if ($pathBaru) {
                $disk->delete($pathBaru);
            }

            throw $error;
        }

        // File lama dihapus setelah perubahan berhasil disimpan.
        if (
            $pathBaru &&
            is_string($pathLama) &&
            trim($pathLama) !== '' &&
            $pathLama !== $pathBaru
        ) {
            $disk->delete($pathLama);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diperbarui.',
            'data' => $repository->fresh(),
        ]);
    }

    public function destroy(Repository $repository)
    {
        $path = $repository->path;

        // Data lama yang tidak memiliki path tetap bisa dihapus.
        if (is_string($path) && trim($path) !== '') {
            Storage::disk('repository')->delete($path);
        }

        $repository->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus.',
        ]);
    }

    public function download(Request $request, Repository $repository)
    {
        abort_unless(
            $this->punyaAkses($request),
            403,
            'Akses berakhir. Masukkan kembali kode repository.'
        );

        $path = $repository->path;

        abort_unless(
            is_string($path) && trim($path) !== '',
            404,
            'Lokasi file tidak tersedia.'
        );

        $disk = Storage::disk('repository');

        abort_unless(
            $disk->exists($path),
            404,
            'File tidak ditemukan.'
        );

        return response()->download(
            $disk->path($path),
            $repository->nama_file,
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    private function rules(bool $update = false): array
    {
        return [
            'mata_pelajaran_id' => $update
                ? 'sometimes|required|integer|exists:mata_pelajaran,id'
                : 'required|integer|exists:mata_pelajaran,id',

            'judul' => $update
                ? 'sometimes|required|string|max:255'
                : 'required|string|max:255',

            'deskripsi' => 'nullable|string|max:5000',

            'file' => [
                $update ? 'nullable' : 'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,zip',
                'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,zip',
                'max:10240',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'judul.required' => 'Judul wajib diisi.',
            'file.required' => 'File wajib dipilih.',
            'file.mimes' => 'Jenis file tidak diizinkan.',
            'file.extensions' => 'Ekstensi file tidak diizinkan.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }

    private function punyaAkses(Request $request): bool
    {
        // Pengguna login tetap bisa mengunduh lewat dashboard.
        if ($request->user()) {
            return true;
        }

        $setting = RepositorySetting::find(1);
        $akses = $request->session()->get('repository_access');

        if (! $setting || ! is_array($akses)) {
            return false;
        }

        return (int) ($akses['versi'] ?? 0) === $setting->versi
            && (int) ($akses['expired_at'] ?? 0) > now()->timestamp;
    }

    public function publik(Request $request)
    {
        abort_unless(
            $this->punyaAkses($request),
            403,
            'Masukkan kode akses untuk membuka repository.'
        );

        $repository = Repository::with('mataPelajaran:id,nama')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $repository,
        ]);
    }

    public function bukaAkses(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:100',
        ]);

        $setting = RepositorySetting::find(1);

        if (! $setting || $data['kode'] !== $setting->kode_akses) {
            return response()->json([
                'message' => 'Kode akses salah atau belum diatur.',
            ], 422);
        }

        $request->session()->regenerate();

        $request->session()->put('repository_access', [
            'versi' => $setting->versi,
            'expired_at' => now()->addHours(2)->timestamp,
        ]);

        return response()->json([
            'message' => 'Akses berhasil dibuka.',
        ]);
    }

    public function tutupAkses(Request $request)
    {
        $request->session()->forget('repository_access');

        return response()->json([
            'message' => 'Akses repository ditutup.',
        ]);
    }

    public function simpanKode(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|min:8|max:100',
        ]);

        $setting = RepositorySetting::firstOrNew(['id' => 1]);

        $setting->kode_akses = $data['kode'];
        $setting->versi = $setting->exists
            ? $setting->versi + 1
            : 1;

        $setting->save();

        return response()->json([
            'message' => 'Kode disimpan. Akses pengunjung sebelumnya dibatalkan.',
        ]);
    }

    public function resetAkses()
    {
        $updated = RepositorySetting::whereKey(1)->increment('versi');

        if (! $updated) {
            return response()->json([
                'message' => 'Atur kode akses repository terlebih dahulu.',
            ], 422);
        }

        return response()->json([
            'message' => 'Akses pengunjung berhasil direset. Kode akses tetap sama.',
        ]);
    }
}
