import axios from 'axios';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { getPengumumanBySlug } from '../../api/pengumuman_api';
import type { PengumumanItem } from './pengumuman-umum';

type Props = {
    slug: string;
};

export default function DetailPengumuman({ slug }: Props) {
    const [item, setItem] = useState<PengumumanItem | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        let aktif = true;

        const load = async () => {
            setLoading(true);
            setError('');
            setItem(null);

            try {
                const data = await getPengumumanBySlug(slug);

                if (aktif) setItem(data);
            } catch (err) {
                if (!aktif) return;

                setError(
                    axios.isAxiosError(err) &&
                    err.response?.status === 404
                        ? 'Pengumuman tidak ditemukan atau sudah tidak tersedia.'
                        : 'Gagal memuat pengumuman.'
                );
            } finally {
                if (aktif) setLoading(false);
            }
        };

        load();

        return () => {
            aktif = false;
        };
    }, [slug]);

    return (
        <>
            <Head title={item?.judul ?? 'Pengumuman'} />

            <main className="mx-auto max-w-3xl px-4 py-10">
                <Link
                    href="/pengumuman"
                    className="text-sm text-blue-700 hover:underline"
                >
                    ← Semua Pengumuman
                </Link>

                {loading ? (
                    <p className="mt-8 text-gray-500">Memuat...</p>
                ) : error ? (
                    <p role="alert" className="mt-8 text-red-600">
                        {error}
                    </p>
                ) : item ? (
                    <article className="mt-6">
                        <h1 className="text-3xl font-bold">
                            {item.judul}
                        </h1>

                        {item.berlaku_sampai && (
                            <p className="mt-3 text-sm text-gray-500">
                                Berlaku sampai:{' '}
                                {new Date(
                                    `${item.berlaku_sampai}T00:00:00`
                                ).toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric',
                                })}
                            </p>
                        )}

                        {item.gambar_url && (
                            <img
                                src={item.gambar_url}
                                alt={item.judul}
                                className="mt-6 h-auto w-full rounded-xl"
                            />
                        )}

                        <div className="mt-6 whitespace-pre-wrap break-words leading-relaxed text-gray-700">
                            {item.isi}
                        </div>
                    </article>
                ) : null}
            </main>
        </>
    );
}