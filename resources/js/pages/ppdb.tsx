import { Head, Link } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { getJadwalPeriodePpdb } from '../../api/periode_ppdb_api';
import Navbar from './navbar';

type PeriodePpdb = {
    id: number;
    nama_periode: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    kuota: number | null;
};

const formatTanggal = (tanggal: string) =>
    new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'long',
        timeZone: 'UTC',
    }).format(new Date(tanggal));

const persyaratan = [
    'Fotokopi ijazah / Surat Keterangan Lulus SD/MI',
    'Fotokopi akta kelahiran',
    'Fotokopi Kartu Keluarga (KK)',
    'Pas foto 3x4 berwarna (3 lembar)',
    'Fotokopi NISN',
    'Fotokopi KIP/PKH (jika ada)',
];

const alur = [
    { no: 1, judul: 'Isi Formulir Online', desc: 'Lengkapi data calon peserta didik melalui form pendaftaran.' },
    { no: 2, judul: 'Verifikasi Berkas', desc: 'Bawa berkas persyaratan ke sekolah untuk diverifikasi panitia.' },
    { no: 3, judul: 'Tes & Wawancara', desc: 'Ikuti tes dan wawancara sesuai jadwal yang ditentukan.' },
    { no: 4, judul: 'Pengumuman & Daftar Ulang', desc: 'Cek hasil pengumuman dan lakukan daftar ulang.' },
];

export default function PpdbIndex() {
    const [periode, setPeriode] = useState<PeriodePpdb[]>([]);
    const [loadingPeriode, setLoadingPeriode] = useState(true);
    const [errorPeriode, setErrorPeriode] = useState('');

    const loadPeriode = useCallback(async () => {
        setLoadingPeriode(true);
        setErrorPeriode('');

        try {
            setPeriode(await getJadwalPeriodePpdb());
        } catch {
            setErrorPeriode('Jadwal PPDB gagal dimuat. Silakan coba lagi.');
        } finally {
            setLoadingPeriode(false);
        }
    }, []);

    useEffect(() => {
        void loadPeriode();
    }, [loadPeriode]);

    return (
        <>
            <Head title="PPDB" />
            <Navbar/>
            {/* Hero */}
            <section className="bg-gradient-to-br from-blue-700 to-blue-900 py-20 text-white">
                <div className="mx-auto max-w-5xl px-4 text-center">
                    <p className="mb-2 text-sm font-semibold tracking-widest uppercase text-blue-200">
                        {periode[0]?.nama_periode || 'Penerimaan Peserta Didik Baru'}
                    </p>
                    <h1 className="mb-4 text-3xl font-bold md:text-5xl">
                        Penerimaan Peserta Didik Baru
                    </h1>
                    <p className="mx-auto mb-8 max-w-2xl text-blue-100">
                        Bergabunglah bersama SMP Angkasa Adi Soemarmo, sekolah berakreditasi A
                        di bawah naungan TNI AU dan Yayasan Ardhya Garini.
                    </p>
                    <Link
                        href="/ppdb/formppdb"
                        className="inline-block rounded-lg bg-yellow-400 px-8 py-3 font-semibold text-blue-900 shadow-lg transition hover:bg-yellow-300"
                    >
                        Daftar Sekarang →
                    </Link>
                </div>
            </section>

            {/* Jadwal */}
            <section className="py-16">
                <div className="mx-auto max-w-5xl px-4">
                    <h2 className="mb-8 text-center text-2xl font-bold text-gray-800">Jadwal PPDB</h2>
                    {loadingPeriode ? (
                        <p className="py-8 text-center text-gray-500" role="status">
                            Memuat jadwal PPDB...
                        </p>
                    ) : errorPeriode ? (
                        <div className="py-8 text-center">
                            <p className="text-red-600" role="alert">{errorPeriode}</p>
                            <button
                                type="button"
                                onClick={() => void loadPeriode()}
                                className="mt-3 text-sm text-blue-700 underline"
                            >
                                Coba lagi
                            </button>
                        </div>
                    ) : periode.length === 0 ? (
                        <p className="py-8 text-center text-gray-500">
                            Belum ada periode PPDB yang dibuka atau dijadwalkan.
                        </p>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2">
                            {periode.map((item) => (
                                <div key={item.id} className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <p className="font-semibold text-blue-800">{item.nama_periode}</p>
                                    <p className="mt-1 text-sm text-gray-600">
                                        {formatTanggal(item.tanggal_mulai)} – {formatTanggal(item.tanggal_selesai)}
                                    </p>
                                    {item.kuota !== null && (
                                        <p className="mt-2 text-sm text-gray-500">Kuota: {item.kuota} peserta</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {/* Persyaratan */}
            <section className="bg-gray-50 py-16">
                <div className="mx-auto max-w-5xl px-4">
                    <h2 className="mb-8 text-center text-2xl font-bold text-gray-800">Persyaratan</h2>
                    <ul className="mx-auto grid max-w-3xl gap-3 md:grid-cols-2">
                        {persyaratan.map((p) => (
                            <li key={p} className="flex items-start gap-3 rounded-lg bg-white p-4 shadow-sm">
                                <span className="mt-0.5 text-green-600">✔</span>
                                <span className="text-gray-700">{p}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            {/* Alur */}
            <section className="py-16">
                <div className="mx-auto max-w-5xl px-4">
                    <h2 className="mb-8 text-center text-2xl font-bold text-gray-800">Alur Pendaftaran</h2>
                    <div className="grid gap-6 md:grid-cols-4">
                        {alur.map((a) => (
                            <div key={a.no} className="text-center">
                                <div className="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-blue-700 text-lg font-bold text-white">
                                    {a.no}
                                </div>
                                <p className="mb-1 font-semibold text-gray-800">{a.judul}</p>
                                <p className="text-sm text-gray-600">{a.desc}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA */}
            <section className="bg-blue-800 py-14 text-center text-white">
                <h2 className="mb-3 text-2xl font-bold">Siap bergabung?</h2>
                <p className="mb-6 text-blue-100">Isi formulir pendaftaran online sekarang juga.</p>
                <Link
                    href="/ppdb/formppdb"
                    className="inline-block rounded-lg bg-yellow-400 px-8 py-3 font-semibold text-blue-900 transition hover:bg-yellow-300"
                >
                    Isi Formulir Pendaftaran
                </Link>
            </section>
        </>
    );
}
