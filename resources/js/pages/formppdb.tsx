import { Head, Link } from '@inertiajs/react';
import { useState, type FormEvent, type ReactNode, useEffect } from 'react';
import { createPpdb } from '../../api/ppdb_api';
import { getPeriodePpdbAktif } from '../../api/periode_ppdb_api';

const initialData = {
    periode_ppdb_id: '',
    nama_lengkap: '',
    nisn: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    asal_sekolah: '',
    nama_orang_tua: '',
    no_hp: '',
    email: '',
    alamat: '',
};
const inputClass =
    'w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-800 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 focus:outline-none';

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string | string[];
    children: ReactNode;
}) {
    const pesan = Array.isArray(error) ? error[0] : error;

    return (
        <div>
            <label className="mb-1 block text-sm font-medium text-gray-700">
                {label}
            </label>

            {children}

            {pesan && (
                <p className="mt-1 text-sm text-red-600">
                    {pesan}
                </p>
            )}
        </div>
    );
}
type PeriodeItem = {
    id: number;
    nama_periode: string;
};

export default function PpdbDaftar() {
    const [data, setFormData] = useState(initialData);
    const [errors, setErrors] = useState<any>({});
    const [processing, setProcessing] = useState(false);
    const [success, setSuccess] = useState(false);
    const [periode, setPeriode] = useState<PeriodeItem[]>([]);
    const [loadingPeriode, setLoadingPeriode] = useState(true);
    const [errorPeriode, setErrorPeriode] = useState('');

    const submit = async (e: FormEvent) => {
        e.preventDefault();

        if (processing) return;

        setErrors({});
        setSuccess(false);

        if (!data.periode_ppdb_id) {
            setErrors({
                periode_ppdb_id: ['Pilih periode pendaftaran terlebih dahulu.'],
            });
            return;
        }

        if (loadingPeriode || errorPeriode || periode.length === 0) {
            return;
        }

        setProcessing(true);

        try {
            await createPpdb({
                ...data,
                periode_ppdb_id: Number(data.periode_ppdb_id),
            });

            setSuccess(true);

            // Kosongkan data pribadi, pertahankan pilihan periode.
            setFormData({
                ...initialData,
                periode_ppdb_id: data.periode_ppdb_id,
            });
        } catch (error: any) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors ?? {});
            } else {
                alert('Gagal mengirim pendaftaran.');
            }
        } finally {
            setProcessing(false);
        }
    };

    const setData = (key: keyof typeof initialData, value: string) => {
        setFormData((previous) => ({
            ...previous,
            [key]: value,
        }));

        setErrors((previous: any) => ({
            ...previous,
            [key]: [],
        }));
    };

    useEffect(() => {
        let aktif = true;

        const loadPeriode = async () => {
            try {
                const items: PeriodeItem[] = await getPeriodePpdbAktif();

                if (!aktif) return;

                setPeriode(items);

                // Pilih otomatis jika hanya satu periode yang dibuka.
                if (items.length === 1) {
                    setFormData((previous) => ({
                        ...previous,
                        periode_ppdb_id: String(items[0].id),
                    }));
                }
            } catch {
                if (aktif) {
                    setErrorPeriode('Gagal memuat periode pendaftaran.');
                }
            } finally {
                if (aktif) {
                    setLoadingPeriode(false);
                }
            }
        };

        void loadPeriode();

        return () => {
            aktif = false;
        };
    }, []);

    return (
        <>
            <Head title="Formulir PPDB" />

            <main className="min-h-screen bg-gray-50 pt-24 pb-16">
                <div className="mx-auto max-w-3xl px-4">
                    <Link href="/ppdb" className="mb-4 inline-block text-sm text-blue-700 hover:underline">
                        ← Kembali ke info PPDB
                    </Link>

                    <div className="rounded-2xl bg-white p-6 shadow-md md:p-10">
                        <h1 className="mb-1 text-2xl font-bold text-gray-800">Formulir Pendaftaran</h1>
                        <p className="mb-8 text-gray-600">Calon Peserta Didik Baru SMP Angkasa Adi Soemarmo</p>

                        {success && (
                            <div className="mb-6 rounded-lg border border-green-300 bg-green-50 p-4 text-green-800">
                                ✅ Pendaftaran berhasil dikirim! Silakan bawa berkas persyaratan ke sekolah untuk verifikasi.
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-5">
                            <h2 className="border-b pb-2 font-semibold text-blue-800">
                                Periode Pendaftaran
                            </h2>

                            <Field
                                label="Pilih Periode PPDB"
                                error={errors.periode_ppdb_id}
                            >
                                <select
                                    aria-label="Periode PPDB"
                                    className={inputClass}
                                    value={data.periode_ppdb_id}
                                    onChange={(event) =>
                                        setData('periode_ppdb_id', event.target.value)
                                    }
                                    required
                                    disabled={
                                        processing ||
                                        loadingPeriode ||
                                        periode.length === 0
                                    }
                                >
                                    <option value="">
                                        {loadingPeriode
                                            ? 'Memuat periode...'
                                            : '-- Pilih periode --'}
                                    </option>

                                    {periode.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.nama_periode}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            {errorPeriode && (
                                <p role="alert" className="text-sm text-red-600">
                                    {errorPeriode}
                                </p>
                            )}

                            {!loadingPeriode && !errorPeriode && periode.length === 0 && (
                                <div className="rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
                                    Saat ini belum ada periode pendaftaran yang dibuka.
                                </div>
                            )}

                            <h2 className="border-b pb-2 font-semibold text-blue-800">
                                Data Calon Siswa
                            </h2>

                            <Field label="Nama Lengkap" error={errors.nama_lengkap}>
                                <input className={inputClass} value={data.nama_lengkap}
                                    onChange={(e) => setData('nama_lengkap', e.target.value)} />
                            </Field>

                            <div className="grid gap-5 md:grid-cols-2">
                                <Field label="NISN" error={errors.nisn}>
                                    <input className={inputClass} inputMode="numeric" maxLength={10} value={data.nisn}
                                        onChange={(e) => setData('nisn', e.target.value.replace(/\D/g, ''))} />
                                </Field>
                                <Field label="Jenis Kelamin" error={errors.jenis_kelamin}>
                                    <select className={inputClass} value={data.jenis_kelamin}
                                        onChange={(e) => setData('jenis_kelamin', e.target.value)}>
                                        <option value="">-- Pilih --</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </Field>
                                <Field label="Tempat Lahir" error={errors.tempat_lahir}>
                                    <input className={inputClass} value={data.tempat_lahir}
                                        onChange={(e) => setData('tempat_lahir', e.target.value)} />
                                </Field>
                                <Field label="Tanggal Lahir" error={errors.tanggal_lahir}>
                                    <input type="date" className={inputClass} value={data.tanggal_lahir}
                                        onChange={(e) => setData('tanggal_lahir', e.target.value)} />
                                </Field>
                            </div>

                            <Field label="Asal Sekolah (SD/MI)" error={errors.asal_sekolah}>
                                <input className={inputClass} value={data.asal_sekolah}
                                    onChange={(e) => setData('asal_sekolah', e.target.value)} />
                            </Field>

                            <h2 className="border-b pt-4 pb-2 font-semibold text-blue-800">Data Orang Tua / Wali</h2>

                            <Field label="Nama Orang Tua / Wali" error={errors.nama_orang_tua}>
                                <input className={inputClass} value={data.nama_orang_tua}
                                    onChange={(e) => setData('nama_orang_tua', e.target.value)} />
                            </Field>

                            <div className="grid gap-5 md:grid-cols-2">
                                <Field label="No. HP / WhatsApp" error={errors.no_hp}>
                                    <input type="tel" className={inputClass} value={data.no_hp}
                                        onChange={(e) => setData('no_hp', e.target.value)} />
                                </Field>
                                <Field label="Email (opsional)" error={errors.email}>
                                    <input type="email" className={inputClass} value={data.email}
                                        onChange={(e) => setData('email', e.target.value)} />
                                </Field>
                            </div>

                            <Field label="Alamat Lengkap" error={errors.alamat}>
                                <textarea rows={3} className={inputClass} value={data.alamat}
                                    onChange={(e) => setData('alamat', e.target.value)} />
                            </Field>

                            <button
                                type="submit"
                                disabled={
                                    processing ||
                                    loadingPeriode ||
                                    Boolean(errorPeriode) ||
                                    periode.length === 0 ||
                                    !data.periode_ppdb_id
                                }
                                className="w-full rounded-lg bg-blue-700 py-3 font-semibold text-white transition hover:bg-blue-800 disabled:opacity-60"
                            >
                                {processing ? 'Mengirim...' : 'Kirim Pendaftaran'}
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </>
    );
}