import { Head } from '@inertiajs/react';
import { ImageOff, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { getAllGaleri } from '../../api/galeri_api';
import PublicPageHeader from '@/components/public-page-header';
import Navbar from './navbar';

type GaleriItem = {
    id: number;
    gambar_url: string | null;
    deskripsi: string | null;
};

export default function GaleriUmum() {
    const [rows, setRows] = useState<GaleriItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [percobaan, setPercobaan] = useState(0);
    const [gambarTerpilih, setGambarTerpilih] = useState<GaleriItem | null>(null);

    useEffect(() => {
        let aktif = true;

        const load = async () => {
            setLoading(true);
            setError('');

            try {
                const data = await getAllGaleri();

                if (aktif) {
                    setRows(data);
                }
            } catch {
                if (aktif) {
                    setError('Gagal memuat galeri. Silakan coba lagi.');
                }
            } finally {
                if (aktif) {
                    setLoading(false);
                }
            }
        };

        void load();

        return () => {
            aktif = false;
        };
    }, [percobaan]);

    useEffect(() => {
        if (!gambarTerpilih) return;

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') setGambarTerpilih(null);
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [gambarTerpilih]);

    return (
        <>
            <Navbar />
            <Head title="Galeri" />

            <PublicPageHeader
                label="Dokumentasi Sekolah"
                title="Galeri Sekolah"
                description="Dokumentasi kegiatan, prestasi, dan momen kebersamaan keluarga besar SMP Angkasa Adi Soemarmo."
            />

            <main className="min-h-screen bg-gray-50">
                <section
                    id="gallery"
                    className="scroll-mt-16 py-12"
                >
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        
                        {loading ? (
                            <p className="py-12 text-center text-gray-500">
                                Memuat galeri...
                            </p>
                        ) : error ? (
                            <div className="py-12 text-center">
                                <p
                                    role="alert"
                                    className="text-red-600"
                                >
                                    {error}
                                </p>
                                <button
                                    onClick={() =>
                                        setPercobaan((value) => value + 1)
                                    }
                                    className="mt-4 rounded-lg bg-blue-700 px-4 py-2 text-sm text-white"
                                >
                                    Coba Lagi
                                </button>
                            </div>
                        ) : rows.length === 0 ? (
                            <p className="py-12 text-center text-gray-500">
                                Belum ada foto galeri.
                            </p>
                        ) : (
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {rows.map((image) => (
                                    <figure
                                        key={image.id}
                                        className="overflow-hidden rounded-xl bg-white shadow-sm"
                                    >
                                        <div className="group overflow-hidden bg-gray-100">
                                            {image.gambar_url ? (
                                                <button
                                                    type="button"
                                                    onClick={() => setGambarTerpilih(image)}
                                                    className="block w-full cursor-zoom-in text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"
                                                    aria-label={`Lihat gambar penuh: ${image.deskripsi || 'Foto kegiatan sekolah'}`}
                                                >
                                                    <img
                                                        src={image.gambar_url}
                                                        alt={
                                                            image.deskripsi ||
                                                            'Foto kegiatan sekolah'
                                                        }
                                                        loading="lazy"
                                                        className="h-64 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                    />
                                                </button>
                                            ) : (
                                                <div className="flex h-64 items-center justify-center">
                                                    <ImageOff className="size-10 text-gray-400" />
                                                </div>
                                            )}
                                        </div>

                                        {image.deskripsi && (
                                            <figcaption className="whitespace-pre-wrap break-words p-4 text-sm leading-relaxed text-gray-700">
                                                {image.deskripsi}
                                            </figcaption>
                                        )}
                                    </figure>
                                ))}
                            </div>
                        )}
                    </div>
                </section>
            </main>

            {gambarTerpilih?.gambar_url && (
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-label={gambarTerpilih.deskripsi || 'Foto kegiatan sekolah'}
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 sm:p-8"
                    onClick={() => setGambarTerpilih(null)}
                >
                    <button
                        type="button"
                        onClick={() => setGambarTerpilih(null)}
                        aria-label="Tutup gambar"
                        className="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    >
                        <X className="size-6" />
                    </button>
                    <img
                        src={gambarTerpilih.gambar_url}
                        alt={gambarTerpilih.deskripsi || 'Foto kegiatan sekolah'}
                        className="max-h-[calc(100vh-2rem)] max-w-full object-contain sm:max-h-[calc(100vh-4rem)]"
                        onClick={(event) => event.stopPropagation()}
                    />
                </div>
            )}
        </>
    );
}
