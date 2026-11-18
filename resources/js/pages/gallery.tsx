import { Head } from '@inertiajs/react';
import { ImageOff, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { getAllGaleri } from '../../api/galeri_api';

type GaleriItem = {
    id: number;
    gambar_url: string | null;
    deskripsi: string | null;
};

export default function Gallery() {
    const [rows, setRows] = useState<GaleriItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [gambarTerpilih, setGambarTerpilih] = useState<GaleriItem | null>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');

        try {
            setRows(await getAllGaleri());
        } catch {
            setError('Gagal memuat galeri. Silakan coba lagi.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        void load();
    }, [load]);

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
            <Head/>

            <section
                id="gallery"
                className="scroll-mt-16 bg-gray-50 py-16"
            >
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-12 text-center">
                        <h1 className="mb-4 text-4xl font-bold text-gray-900">
                            Galeri
                        </h1>

                        <p className="text-lg text-gray-600">
                            Galeri seputar penghargaan maupun aktivitas
                            yang dilaksanakan di SMP Angkasa Adi Soemarmo.
                        </p>
                    </div>

                    {loading ? (
                        <p className="py-10 text-center text-gray-500">
                            Memuat galeri...
                        </p>
                    ) : error ? (
                        <div className="py-10 text-center">
                            <p role="alert" className="text-red-600">
                                {error}
                            </p>

                            <button
                                onClick={() => void load()}
                                className="mt-3 text-sm text-blue-700 underline"
                            >
                                Coba lagi
                            </button>
                        </div>
                    ) : rows.length === 0 ? (
                        <p className="py-10 text-center text-gray-500">
                            Belum ada foto galeri.
                        </p>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {rows.map((image) => (
                                <figure
                                    key={image.id}
                                    className="group relative overflow-hidden rounded-lg shadow-md transition-shadow hover:shadow-xl"
                                >
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
                                        <div className="flex h-64 items-center justify-center bg-gray-200">
                                            <ImageOff className="size-10 text-gray-400" />
                                        </div>
                                    )}

                                    {image.deskripsi && (
                                        <figcaption
                                            className="
                                                pointer-events-none absolute inset-0 flex items-end
                                                bg-gradient-to-t from-black/80
                                                via-black/20 to-transparent p-4
                                                opacity-100 transition-opacity
                                                duration-300
                                                [@media(hover:hover)]:opacity-0
                                                [@media(hover:hover)]:group-hover:opacity-100
                                            "
                                        >
                                            <p className="max-h-full overflow-y-auto whitespace-pre-wrap break-words text-sm font-medium text-white">
                                                {image.deskripsi}
                                            </p>
                                        </figcaption>
                                    )}
                                </figure>
                            ))}
                        </div>
                    )}
                </div>
            </section>

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
