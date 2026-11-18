import { Head, Link } from '@inertiajs/react';
import { ImageOff } from 'lucide-react';
import { useEffect, useState } from 'react';
import { getPengumumanPublik } from '../../api/pengumuman_api';
import PublicPageHeader from '@/components/public-page-header';
import Navbar from './navbar';

export type PengumumanItem = {
    id: number;
    judul: string;
    slug: string;
    isi: string;
    gambar_url: string | null;
    target: string;
    berlaku_sampai: string | null;
};

export default function PengumumanPublik() {
    const [rows, setRows] = useState<PengumumanItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        let aktif = true;

        const load = async () => {
            try {
                const data = await getPengumumanPublik();

                if (aktif) setRows(data);
            } catch {
                if (aktif) {
                    setError('Gagal memuat pengumuman. Silakan coba lagi.');
                }
            } finally {
                if (aktif) setLoading(false);
            }
        };

        load();

        return () => {
            aktif = false;
        };
    }, []);

    return (
        <>
            <Navbar />
            <Head title="Pengumuman Sekolah" />

            <PublicPageHeader
                label="Informasi Sekolah"
                title="Pengumuman Sekolah"
                description="Temukan informasi terbaru mengenai kegiatan akademik, penerimaan peserta didik baru, dan pemberitahuan resmi SMP Angkasa Adi Soemarmo."
            />

            <main className="mx-auto max-w-6xl px-4 py-10">

                {loading ? (
                    <p className="mt-8 text-gray-500">
                        Memuat pengumuman...
                    </p>
                ) : error ? (
                    <p role="alert" className="mt-8 text-red-600">
                        {error}
                    </p>
                ) : rows.length === 0 ? (
                    <p className="mt-8 text-gray-500">
                        Belum ada pengumuman.
                    </p>
                ) : (
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {rows.map((item) => (
                            <Link
                                key={item.id}
                                href={`/pengumuman/${item.slug}`}
                                className="overflow-hidden rounded-xl border bg-white transition hover:shadow-lg"
                            >
                                {item.gambar_url ? (
                                    <img
                                        src={item.gambar_url}
                                        alt={item.judul}
                                        loading="lazy"
                                        className="aspect-video w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex aspect-video items-center justify-center bg-gray-100">
                                        <ImageOff className="size-10 text-gray-400" />
                                    </div>
                                )}

                                <div className="p-5">
                                    <h2 className="text-lg font-semibold text-gray-900">
                                        {item.judul}
                                    </h2>

                                    <p className="mt-3 line-clamp-3 text-sm text-gray-600">
                                        {item.isi}
                                    </p>

                                    <p className="mt-4 text-sm font-medium text-blue-700">
                                        Baca selengkapnya →
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </main>
        </>
    );
}