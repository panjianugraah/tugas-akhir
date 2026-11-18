import { Head } from '@inertiajs/react';
import { FileDown } from 'lucide-react';
import { useEffect, useState } from 'react';
import { getAllPeriodePpdb } from '../../../api/periode_ppdb_api';
import { getAllKelas } from '../../../api/kelas_api';

type Periode = {
    id: number;
    nama_periode: string;
};

type Kelas = {
    id: number;
    nama_kelas: string;
    tahun_ajaran: string;
};

const selectClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm';

const buttonClass =
    'mt-5 inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50';

export default function AdminLaporan() {
    const [periode, setPeriode] = useState<Periode[]>([]);
    const [kelas, setKelas] = useState<Kelas[]>([]);

    const [loadingPeriode, setLoadingPeriode] = useState(true);
    const [loadingKelas, setLoadingKelas] = useState(true);

    const [errorPeriode, setErrorPeriode] = useState('');
    const [errorKelas, setErrorKelas] = useState('');

    useEffect(() => {
        let aktif = true;

        getAllPeriodePpdb()
            .then((data) => {
                if (aktif) setPeriode(data);
            })
            .catch(() => {
                if (aktif) {
                    setErrorPeriode('Gagal memuat pilihan periode.');
                }
            })
            .finally(() => {
                if (aktif) setLoadingPeriode(false);
            });

        getAllKelas()
            .then((data) => {
                if (aktif) setKelas(data);
            })
            .catch(() => {
                if (aktif) {
                    setErrorKelas('Gagal memuat pilihan kelas.');
                }
            })
            .finally(() => {
                if (aktif) setLoadingKelas(false);
            });

        return () => {
            aktif = false;
        };
    }, []);

    return (
        <>
            <Head title="Laporan Sekolah" />

            <main className="p-4 md:p-6">
                <h1 className="text-2xl font-bold">
                    Laporan Sekolah
                </h1>

                <p className="mt-2 text-sm text-muted-foreground">
                    Pilih filter, kemudian unduh laporan dalam format PDF.
                </p>

                <div className="mt-6 grid gap-6 lg:grid-cols-3">
                    {/* Laporan PPDB */}
                    <form
                        action="/admin/laporan/ppdb/pdf"
                        method="GET"
                        target="_blank"
                        rel="noopener"
                        className="rounded-xl border p-5"
                    >
                        <h2 className="text-lg font-semibold">
                            Laporan PPDB
                        </h2>

                        <p className="mt-2 text-sm text-muted-foreground">
                            Daftar pendaftar berdasarkan periode dan status.
                        </p>

                        <label
                            htmlFor="laporan-periode"
                            className="mb-1 mt-5 block text-sm font-medium"
                        >
                            Periode PPDB
                        </label>

                        <select
                            id="laporan-periode"
                            name="periode_ppdb_id"
                            required
                            defaultValue=""
                            disabled={loadingPeriode}
                            className={selectClass}
                        >
                            <option value="">
                                {loadingPeriode
                                    ? 'Memuat periode...'
                                    : 'Pilih periode'}
                            </option>

                            {periode.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.nama_periode}
                                </option>
                            ))}
                        </select>

                        {errorPeriode && (
                            <p role="alert" className="mt-2 text-xs text-red-600">
                                {errorPeriode}
                            </p>
                        )}

                        <label
                            htmlFor="laporan-status-ppdb"
                            className="mb-1 mt-4 block text-sm font-medium"
                        >
                            Status
                        </label>

                        <select
                            id="laporan-status-ppdb"
                            name="status"
                            className={selectClass}
                        >
                            <option value="">Semua status</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="diterima">Diterima</option>
                            <option value="ditolak">Ditolak</option>
                        </select>

                        <button
                            type="submit"
                            disabled={loadingPeriode || periode.length === 0}
                            className={buttonClass}
                        >
                            <FileDown className="size-4" />
                            Generate PDF PPDB
                        </button>
                    </form>

                    {/* Laporan siswa */}
                    <form
                        action="/admin/laporan/siswa/pdf"
                        method="GET"
                        target="_blank"
                        rel="noopener"
                        className="rounded-xl border p-5"
                    >
                        <h2 className="text-lg font-semibold">
                            Laporan Siswa
                        </h2>

                        <p className="mt-2 text-sm text-muted-foreground">
                            Daftar siswa berdasarkan kelas dan status.
                        </p>

                        <label
                            htmlFor="laporan-kelas"
                            className="mb-1 mt-5 block text-sm font-medium"
                        >
                            Kelas
                        </label>

                        <select
                            id="laporan-kelas"
                            name="kelas_id"
                            disabled={loadingKelas}
                            className={selectClass}
                        >
                            <option value="">Semua kelas</option>

                            {kelas.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.nama_kelas} ({item.tahun_ajaran})
                                </option>
                            ))}
                        </select>

                        {errorKelas && (
                            <p role="alert" className="mt-2 text-xs text-red-600">
                                {errorKelas}
                            </p>
                        )}

                        <label
                            htmlFor="laporan-status-siswa"
                            className="mb-1 mt-4 block text-sm font-medium"
                        >
                            Status
                        </label>

                        <select
                            id="laporan-status-siswa"
                            name="status"
                            className={selectClass}
                        >
                            <option value="">Semua status</option>
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                        </select>

                        <button
                            type="submit"
                            disabled={loadingKelas || Boolean(errorKelas)}
                            className={buttonClass}
                        >
                            <FileDown className="size-4" />
                            Generate PDF Siswa
                        </button>
                    </form>

                    {/* Laporan guru */}
                    <form
                        action="/admin/laporan/guru/pdf"
                        method="GET"
                        target="_blank"
                        rel="noopener"
                        className="rounded-xl border p-5"
                    >
                        <h2 className="text-lg font-semibold">
                            Laporan Guru
                        </h2>

                        <p className="mt-2 text-sm text-muted-foreground">
                            Daftar guru berdasarkan status keaktifan.
                        </p>

                        <label
                            htmlFor="laporan-status-guru"
                            className="mb-1 mt-5 block text-sm font-medium"
                        >
                            Status
                        </label>

                        <select
                            id="laporan-status-guru"
                            name="status"
                            className={selectClass}
                        >
                            <option value="">Semua status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>

                        <button type="submit" className={buttonClass}>
                            <FileDown className="size-4" />
                            Generate PDF Guru
                        </button>
                    </form>
                </div>
            </main>
        </>
    );
}