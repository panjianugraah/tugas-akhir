import { Head, Link, router } from '@inertiajs/react';
import {
    BookOpen,
    ClipboardList,
    FileDown,
    GraduationCap,
    Users,
} from 'lucide-react';
import { useState } from 'react';

type StatusItem = {
    key: 'menunggu' | 'diterima' | 'ditolak';
    label: string;
    jumlah: number;
};

type Props = {
    ringkasan: {
        siswa_aktif: number;
        guru_aktif: number;
        total_kelas: number;
        pendaftar_ppdb: number;
    };

    periode: {
        id: number;
        nama_periode: string;
    }[];

    periodeTerpilih: number | null;

    grafikStatus: StatusItem[];

    grafikKelas: {
        id: number;
        label: string;
        jumlah: number;
    }[];

    pendaftarTerbaru: {
        id: number;
        nama_lengkap: string;
        asal_sekolah: string;
        status: string;
    }[];

    siswaTanpaKelas: number;
};

const statusColors: Record<string, string> = {
    menunggu: 'bg-amber-100 text-amber-800',
    diterima: 'bg-green-100 text-green-800',
    ditolak: 'bg-red-100 text-red-800',
};

const barColors: Record<string, string> = {
    menunggu: 'bg-amber-500',
    diterima: 'bg-green-600',
    ditolak: 'bg-red-500',
};

const formatNumber = (value: number) =>
    value.toLocaleString('id-ID');

export default function Dashboard({
    ringkasan,
    periode,
    periodeTerpilih,
    grafikStatus,
    grafikKelas,
    pendaftarTerbaru,
    siswaTanpaKelas,
}: Props) {
    const [loadingFilter, setLoadingFilter] = useState(false);

    const namaPeriode =
        periode.find((item) => item.id === periodeTerpilih)?.nama_periode
        ?? 'Belum ada periode';

    const maksimumStatus = Math.max(
        1,
        ...grafikStatus.map((item) => item.jumlah),
    );

    const maksimumKelas = Math.max(
        1,
        ...grafikKelas.map((item) => item.jumlah),
    );

    const cards = [
        {
            title: 'Siswa Aktif',
            value: ringkasan.siswa_aktif,
            note: 'Seluruh siswa berstatus aktif',
            icon: GraduationCap,
            color: 'bg-blue-100 text-blue-700',
            href: '/admin/siswa',
        },
        {
            title: 'Guru Aktif',
            value: ringkasan.guru_aktif,
            note: 'Seluruh guru berstatus aktif',
            icon: Users,
            color: 'bg-green-100 text-green-700',
            href: '/admin/guru',
        },
        {
            title: 'Total Kelas',
            value: ringkasan.total_kelas,
            note: 'Semua tahun ajaran yang tersimpan',
            icon: BookOpen,
            color: 'bg-purple-100 text-purple-700',
            href: '/admin/kelas',
        },
        {
            title: 'Pendaftar PPDB',
            value: ringkasan.pendaftar_ppdb,
            note: namaPeriode,
            icon: ClipboardList,
            color: 'bg-amber-100 text-amber-700',
            href: '/admin/ppdbadmin',
        },
    ];

    const gantiPeriode = (id: string) => {
        if (!id || loadingFilter) return;

        router.get(
            '/admin/dashboard',
            {
                periode_ppdb_id: id,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setLoadingFilter(true),
                onFinish: () => setLoadingFilter(false),
            },
        );
    };

    return (
        <>
            <Head title="Dashboard Admin" />

            <main className="flex flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Dashboard Sekolah
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Ringkasan data SMP Angkasa Adi Soemarmo.
                        </p>
                    </div>

                    <Link
                        href="/admin/laporan"
                        className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800"
                    >
                        <FileDown className="size-4" />
                        Generate Laporan
                    </Link>
                </div>

                {/* Filter PPDB */}
                <div className="rounded-xl border bg-background p-4">
                    <label
                        htmlFor="dashboard-periode"
                        className="mb-2 block text-sm font-medium"
                    >
                        Periode untuk ringkasan PPDB
                    </label>

                    <select
                        id="dashboard-periode"
                        value={periodeTerpilih ?? ''}
                        onChange={(event) =>
                            gantiPeriode(event.target.value)
                        }
                        disabled={loadingFilter || periode.length === 0}
                        className="w-full rounded-lg border bg-background px-3 py-2 text-sm sm:max-w-sm"
                    >
                        {periode.length === 0 && (
                            <option value="">Belum ada periode PPDB</option>
                        )}

                        {periode.map((item) => (
                            <option key={item.id} value={item.id}>
                                {item.nama_periode}
                            </option>
                        ))}
                    </select>

                    <p className="mt-2 text-xs text-muted-foreground">
                        Filter ini hanya mengubah data PPDB,
                        bukan data siswa, guru, atau kelas.
                    </p>

                    {loadingFilter && (
                        <p role="status" className="mt-2 text-xs text-blue-700">
                            Memuat periode...
                        </p>
                    )}
                </div>

                {/* Kartu ringkasan */}
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {cards.map((card) => {
                        const Icon = card.icon;

                        return (
                            <Link
                                key={card.title}
                                href={card.href}
                                className="rounded-xl border bg-background p-5 transition hover:shadow-md"
                            >
                                <div className="flex items-center justify-between">
                                    <p className="text-sm text-muted-foreground">
                                        {card.title}
                                    </p>

                                    <div className={`rounded-lg p-2 ${card.color}`}>
                                        <Icon className="size-5" />
                                    </div>
                                </div>

                                <p className="mt-4 text-3xl font-bold">
                                    {formatNumber(card.value)}
                                </p>

                                <p className="mt-2 text-xs text-muted-foreground">
                                    {card.note}
                                </p>
                            </Link>
                        );
                    })}
                </div>

                {/* Grafik */}
                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="rounded-xl border bg-background p-5">
                        <h2 className="text-lg font-semibold">
                            Status Pendaftar PPDB
                        </h2>

                        <p className="mt-1 text-sm text-muted-foreground">
                            {namaPeriode}
                        </p>

                        {ringkasan.pendaftar_ppdb === 0 ? (
                            <p className="py-10 text-center text-sm text-muted-foreground">
                                Belum ada pendaftar pada periode ini.
                            </p>
                        ) : (
                            <div className="mt-6 space-y-5">
                                {grafikStatus.map((item) => (
                                    <div key={item.key}>
                                        <div className="mb-2 flex justify-between gap-3 text-sm">
                                            <span>{item.label}</span>
                                            <span className="font-semibold">
                                                {formatNumber(item.jumlah)} orang
                                            </span>
                                        </div>

                                        <div
                                            role="img"
                                            aria-label={`${item.label}: ${item.jumlah} pendaftar`}
                                            className="h-4 overflow-hidden rounded-full bg-muted"
                                        >
                                            <div
                                                className={`h-full rounded-full ${barColors[item.key]}`}
                                                style={{
                                                    width: `${(item.jumlah / maksimumStatus) * 100
                                                        }%`,
                                                }}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        <p className="mt-5 text-xs text-muted-foreground">
                            Panjang batang menunjukkan jumlah orang,
                            bukan persentase.
                        </p>
                    </section>

                    <section className="rounded-xl border bg-background p-5">
                        <h2 className="text-lg font-semibold">
                            Siswa Aktif per Kelas
                        </h2>

                        <p className="mt-1 text-sm text-muted-foreground">
                            Maksimal 6 kelas dengan siswa aktif terbanyak.
                        </p>

                        {grafikKelas.length === 0 ? (
                            <p className="py-10 text-center text-sm text-muted-foreground">
                                Belum ada data kelas.
                            </p>
                        ) : (
                            <div className="mt-6 space-y-4">
                                {grafikKelas.map((item) => (
                                    <div key={item.id}>
                                        <div className="mb-2 flex justify-between gap-3 text-sm">
                                            <span>{item.label}</span>
                                            <span className="shrink-0 font-semibold">
                                                {formatNumber(item.jumlah)} siswa
                                            </span>
                                        </div>

                                        <div
                                            role="img"
                                            aria-label={`${item.label}: ${item.jumlah} siswa aktif`}
                                            className="h-3 overflow-hidden rounded-full bg-muted"
                                        >
                                            <div
                                                className="h-full rounded-full bg-blue-600"
                                                style={{
                                                    width: `${(item.jumlah / maksimumKelas) * 100
                                                        }%`,
                                                }}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        <p className="mt-5 text-xs text-muted-foreground">
                            Siswa aktif tanpa kelas: {formatNumber(siswaTanpaKelas)}.
                            Grafik bukan keseluruhan jumlah siswa apabila
                            terdapat lebih dari 6 kelas.
                        </p>
                    </section>
                </div>

                {/* Pendaftar terbaru */}
                <section className="overflow-hidden rounded-xl border bg-background">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b p-5">
                        <div>
                            <h2 className="text-lg font-semibold">
                                Pendaftar Terbaru
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Maksimal 5 pendaftar — {namaPeriode}
                            </p>
                        </div>

                        <Link
                            href="/admin/ppdbadmin"
                            className="text-sm font-medium text-blue-700 hover:underline"
                        >
                            Kelola PPDB →
                        </Link>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <th className="p-3">No</th>
                                    <th className="p-3">Nama</th>
                                    <th className="p-3">Asal Sekolah</th>
                                    <th className="p-3">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                {pendaftarTerbaru.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={4}
                                            className="p-6 text-center text-muted-foreground"
                                        >
                                            Belum ada pendaftar pada periode ini.
                                        </td>
                                    </tr>
                                ) : (
                                    pendaftarTerbaru.map((item, index) => (
                                        <tr key={item.id} className="border-t">
                                            <td className="p-3">{index + 1}</td>
                                            <td className="p-3 font-medium">
                                                {item.nama_lengkap}
                                            </td>
                                            <td className="p-3">
                                                {item.asal_sekolah}
                                            </td>
                                            <td className="p-3">
                                                <span
                                                    className={`rounded-full px-2 py-1 text-xs font-medium capitalize ${statusColors[item.status]
                                                        ?? 'bg-gray-100 text-gray-700'
                                                        }`}
                                                >
                                                    {item.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </>
    );
}