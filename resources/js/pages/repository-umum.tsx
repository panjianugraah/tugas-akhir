import axios from 'axios';
import { Head, Link } from '@inertiajs/react';
import { Download, LockKeyhole } from 'lucide-react';
import { useCallback, useEffect, useState, type FormEvent } from 'react';
import PublicPageHeader from '@/components/public-page-header';
import { bukaAksesRepository, getRepositoryPublik, tutupAksesRepository } from '../../api/repository_api';
import Navbar from './navbar';

type RepositoryItem = {
    id: number;
    judul: string;
    deskripsi: string | null;
    nama_asli: string;
    ekstensi: string;
    ukuran: number;
};

export default function RepositoryUmum() {
    const [rows, setRows] = useState<RepositoryItem[]>([]);
    const [diizinkan, setDiizinkan] = useState(false);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    const [kode, setKode] = useState('');
    const [search, setSearch] = useState('');
    const [error, setError] = useState('');

    const load = useCallback(async () => {
        setLoading(true);
        setError('');

        try {
            setRows(await getRepositoryPublik());
            setDiizinkan(true);
        } catch (err) {
            setRows([]);
            setDiizinkan(false);

            // 403 berarti pengunjung perlu memasukkan kode.
            if (
                !axios.isAxiosError(err) ||
                err.response?.status !== 403
            ) {
                setError('Gagal memuat repository. Silakan coba lagi.');
            }
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        void load();
    }, [load]);

    const buka = async (event: FormEvent) => {
        event.preventDefault();
        if (saving) return;

        setSaving(true);
        setError('');

        try {
            await bukaAksesRepository(kode);
            setKode('');
            await load();
        } catch (err) {
            setError(
                axios.isAxiosError(err)
                    ? err.response?.status === 429
                        ? 'Terlalu banyak percobaan. Tunggu satu menit.'
                        : err.response?.data?.message ?? 'Gagal membuka akses.'
                    : 'Gagal membuka akses.'
            );
        } finally {
            setSaving(false);
        }
    };

    const keluar = async () => {
        try {
            await tutupAksesRepository();
            setDiizinkan(false);
            setRows([]);
            setSearch('');
            setError('');
        } catch {
            setError('Gagal menutup akses.');
        }
    };

    const filtered = rows.filter((item) =>
        `${item.judul} ${item.deskripsi ?? ''} ${item.nama_asli}`
            .toLowerCase()
            .includes(search.toLowerCase())
    );

    return (
        <>
            <Navbar />
            <Head title="Repository Sekolah" />

            <PublicPageHeader
                label="Sumber Belajar Sekolah"
                title="Repository Sekolah"
                description="Akses materi pembelajaran dan dokumen pendukung kegiatan sekolah. Gunakan kode akses yang diberikan oleh pihak sekolah untuk membuka repository."
            />

            <main className="mx-auto min-h-screen max-w-6xl px-4 py-10">

                {error && (
                    <p role="alert" className="mt-4 text-sm text-red-600">
                        {error}
                    </p>
                )}

                {loading ? (
                    <p className="mt-8 text-gray-500">Memuat repository...</p>
                ) : !diizinkan ? (
                    <form
                        onSubmit={buka}
                        className="mx-auto mt-10 max-w-md rounded-xl border p-6"
                    >
                        <LockKeyhole className="size-8 text-blue-700" />

                        <h2 className="mt-4 text-lg font-semibold">
                            Masukkan Kode Akses
                        </h2>

                        <p className="mt-2 text-sm text-gray-500">
                            Mintalah kode kepada pihak sekolah.
                        </p>

                        <input
                            type="password"
                            aria-label="Kode akses repository"
                            autoComplete="off"
                            value={kode}
                            onChange={(event) => setKode(event.target.value)}
                            required
                            maxLength={100}
                            placeholder="Kode akses"
                            className="mt-4 w-full rounded-lg border px-3 py-2"
                        />

                        <button
                            disabled={saving}
                            className="mt-4 w-full rounded-lg bg-blue-700 px-4 py-2 text-white disabled:opacity-50"
                        >
                            {saving ? 'Memeriksa...' : 'Buka Repository'}
                        </button>
                    </form>
                ) : (
                    <>
                        <div className="mt-8 flex flex-wrap justify-between gap-3">
                            <input
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                aria-label="Cari dokumen"
                                placeholder="Cari dokumen..."
                                className="w-full max-w-sm rounded-lg border px-3 py-2"
                            />
                        </div>

                        <div className="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {filtered.map((item) => (
                                <article
                                    key={item.id}
                                    className="flex flex-col rounded-xl border p-5"
                                >
                                    <span className="text-xs font-bold uppercase text-blue-700">
                                        {item.ekstensi}
                                    </span>

                                    <h2 className="mt-2 font-semibold">
                                        {item.judul}
                                    </h2>

                                    {item.deskripsi && (
                                        <p className="mt-2 whitespace-pre-wrap break-words text-sm text-gray-600">
                                            {item.deskripsi}
                                        </p>
                                    )}

                                    <p className="mt-3 break-words text-xs text-gray-500">
                                        {item.nama_asli}
                                    </p>

                                    <p className="mb-4 mt-1 text-xs text-gray-500">
                                        {(item.ukuran / 1024 / 1024).toFixed(2)} MB
                                    </p>

                                    <a
                                        href={`/api/repository/${item.id}/download`}
                                        className="mt-auto inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-3 py-2 text-sm text-white"
                                    >
                                        <Download className="size-4" />
                                        Unduh
                                    </a>
                                </article>
                            ))}
                        </div>

                        {filtered.length === 0 && (
                            <p className="mt-8 text-gray-500">
                                Tidak ada dokumen.
                            </p>
                        )}
                    </>
                )}
            </main>
        </>
    );
}