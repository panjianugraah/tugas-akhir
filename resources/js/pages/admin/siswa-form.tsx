import axios from 'axios';
import { X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { createSiswa, updateSiswa } from '../../../api/siswa_api';
import { getAllKelas } from '../../../api/kelas_api';

export type SiswaItem = {
    id: number;
    nis: string;
    nisn: string;
    nama_lengkap: string;
    kelas_id: number | null;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    nama_orang_tua: string;
    no_hp: string;
    email: string | null;
    alamat: string;
    status: string;
    kelas?: {
        id: number;
        nama_kelas: string;
        tahun_ajaran: string;
    } | null;
};

type KelasOption = {
    id: number;
    nama_kelas: string;
    tahun_ajaran: string;
};

type FormData = {
    nis: string;
    nisn: string;
    nama_lengkap: string;
    kelas_id: string;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    nama_orang_tua: string;
    no_hp: string;
    email: string;
    alamat: string;
    status: string;
};

type Props = {
    open: boolean;
    data: SiswaItem | null;
    onClose: () => void;
    onSaved: () => void | Promise<void>;
};

const initialData: FormData = {
    nis: '',
    nisn: '',
    nama_lengkap: '',
    kelas_id: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    nama_orang_tua: '',
    no_hp: '',
    email: '',
    alamat: '',
    status: 'aktif',
};

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200';

const inputFields = [
    {
        name: 'nis',
        label: 'NIS',
        type: 'text',
        required: true,
        maxLength: 6,
    },
    {
        name: 'nisn',
        label: 'NISN',
        type: 'text',
        required: true,
        maxLength: 10,
    },
    {
        name: 'nama_lengkap',
        label: 'Nama Lengkap',
        type: 'text',
        required: true,
        maxLength: 255,
    },
    {
        name: 'tempat_lahir',
        label: 'Tempat Lahir',
        type: 'text',
        required: true,
        maxLength: 100,
    },
    {
        name: 'tanggal_lahir',
        label: 'Tanggal Lahir',
        type: 'date',
        required: true,
    },
    {
        name: 'nama_orang_tua',
        label: 'Nama Orang Tua / Wali',
        type: 'text',
        required: true,
        maxLength: 255,
    },
    {
        name: 'no_hp',
        label: 'Nomor HP',
        type: 'tel',
        required: true,
        maxLength: 20,
    },
    {
        name: 'email',
        label: 'Email',
        type: 'email',
        required: false,
        maxLength: 255,
    },
] satisfies {
    name: keyof FormData;
    label: string;
    type: string;
    required: boolean;
    maxLength?: number;
}[];

export default function SiswaForm({
    open,
    data,
    onClose,
    onSaved,
}: Props) {
    const [form, setForm] = useState<FormData>(initialData);
    const [kelas, setKelas] = useState<KelasOption[]>([]);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);
    const [loadingKelas, setLoadingKelas] = useState(false);
    const [kelasError, setKelasError] = useState('');

    useEffect(() => {
        if (!open) return;

        setErrors({});
        setError('');

        setForm(
            data
                ? {
                    nis: data.nis,
                    nisn: data.nisn,
                    nama_lengkap: data.nama_lengkap,
                    kelas_id:
                        data.kelas_id !== null
                            ? String(data.kelas_id)
                            : '',
                    jenis_kelamin: data.jenis_kelamin,
                    tempat_lahir: data.tempat_lahir,
                    tanggal_lahir: data.tanggal_lahir?.slice(0, 10) ?? '',
                    nama_orang_tua: data.nama_orang_tua,
                    no_hp: data.no_hp,
                    email: data.email ?? '',
                    alamat: data.alamat,
                    status: data.status,
                }
                : { ...initialData },
        );
    }, [open, data]);

    useEffect(() => {
        if (!open) return;

        let active = true;

        const loadKelas = async () => {
            setLoadingKelas(true);
            setKelasError('');

            try {
                const result = await getAllKelas();

                if (!Array.isArray(result)) {
                    throw new Error('Format data kelas tidak sesuai.');
                }

                if (active) {
                    setKelas(result);
                }
            } catch {
                if (active) {
                    setKelasError('Gagal memuat pilihan kelas.');
                }
            } finally {
                if (active) {
                    setLoadingKelas(false);
                }
            }
        };

        void loadKelas();

        return () => {
            active = false;
        };
    }, [open]);

    const setData = (key: keyof FormData, value: string) => {
        setForm((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (saving || loadingKelas || kelasError) return;

        setSaving(true);
        setErrors({});
        setError('');

        const payload = {
            ...form,
            nama_lengkap: form.nama_lengkap.trim(),
            nis: form.nis.trim(),
            nisn: form.nisn.trim(),
            kelas_id: form.kelas_id ? Number(form.kelas_id) : null,
            email: form.email.trim() || null,
        };

        try {
            if (data) {
                await updateSiswa(data.id, payload);
            } else {
                await createSiswa(payload);
            }
        } catch (err) {
            if (axios.isAxiosError(err)) {
                if (err.response?.status === 422) {
                    setErrors(err.response.data.errors ?? {});
                }

                setError(
                    err.response?.data?.message ??
                    'Gagal menyimpan data siswa.',
                );
            } else {
                setError('Gagal menyimpan data siswa.');
            }

            setSaving(false);
            return;
        }

        setSaving(false);
        onClose();
        await onSaved();
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="siswa-form-title"
                className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-background p-6 shadow-xl"
            >
                <div className="mb-5 flex items-center justify-between">
                    <h2
                        id="siswa-form-title"
                        className="text-xl font-semibold"
                    >
                        {data ? 'Edit Siswa' : 'Tambah Siswa'}
                    </h2>

                    <button
                        type="button"
                        onClick={onClose}
                        disabled={saving}
                        aria-label="Tutup form"
                        className="rounded p-2 hover:bg-muted disabled:opacity-50"
                    >
                        <X className="size-5" />
                    </button>
                </div>

                {error && (
                    <p
                        role="alert"
                        className="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700"
                    >
                        {error}
                    </p>
                )}

                <form onSubmit={submit}>
                    <fieldset disabled={saving}>
                        <div className="grid gap-4 sm:grid-cols-2">
                            {inputFields.map((field) => (
                                <div key={field.name}>
                                    <label
                                        htmlFor={`siswa-${field.name}`}
                                        className="mb-1 block text-sm font-medium"
                                    >
                                        {field.label}
                                        {field.required && (
                                            <span className="text-red-600">
                                                {' '}*
                                            </span>
                                        )}
                                    </label>

                                    <input
                                        id={`siswa-${field.name}`}
                                        type={field.type}
                                        value={form[field.name]}
                                        onChange={(e) =>
                                            setData(
                                                field.name,
                                                field.name === 'nis' || field.name === 'nisn'
                                                    ? e.target.value.replace(/\D/g, '')
                                                    : e.target.value,
                                            )
                                        }
                                        inputMode={
                                            field.name === 'nis' || field.name === 'nisn'
                                                ? 'numeric'
                                                : undefined
                                        }
                                        pattern={
                                            field.name === 'nis'
                                                ? '[0-9]{6}'
                                                : field.name === 'nisn'
                                                    ? '[0-9]{10}'
                                                    : undefined
                                        }
                                        required={field.required}
                                        maxLength={field.maxLength}
                                        className={inputClass}
                                    />

                                    {errors[field.name] && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors[field.name][0]}
                                        </p>
                                    )}
                                </div>
                            ))}

                            <div>
                                <label
                                    htmlFor="siswa-jenis-kelamin"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Jenis Kelamin *
                                </label>

                                <select
                                    id="siswa-jenis-kelamin"
                                    value={form.jenis_kelamin}
                                    onChange={(e) =>
                                        setData('jenis_kelamin', e.target.value)
                                    }
                                    required
                                    className={inputClass}
                                >
                                    <option value="">-- Pilih --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>

                                {errors.jenis_kelamin && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.jenis_kelamin[0]}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="siswa-kelas"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Kelas
                                </label>

                                <select
                                    id="siswa-kelas"
                                    value={form.kelas_id}
                                    onChange={(e) =>
                                        setData('kelas_id', e.target.value)
                                    }
                                    disabled={saving || loadingKelas}
                                    className={inputClass}
                                >
                                    <option value="">
                                        {loadingKelas
                                            ? 'Memuat kelas...'
                                            : 'Belum ditentukan'}
                                    </option>

                                    {kelas.map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {item.nama_kelas} — {item.tahun_ajaran}
                                        </option>
                                    ))}
                                </select>

                                {kelasError && (
                                    <p
                                        role="alert"
                                        className="mt-1 text-sm text-red-600"
                                    >
                                        {kelasError} Tutup dan buka kembali
                                        form untuk mencoba lagi.
                                    </p>
                                )}

                                {errors.kelas_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.kelas_id[0]}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="siswa-status"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Status *
                                </label>

                                <select
                                    id="siswa-status"
                                    value={form.status}
                                    onChange={(e) =>
                                        setData('status', e.target.value)
                                    }
                                    required
                                    className={inputClass}
                                >
                                    <option value="aktif">Aktif</option>
                                    <option value="lulus">Lulus</option>
                                    <option value="pindah">Pindah</option>
                                    <option value="keluar">Keluar</option>
                                </select>

                                {errors.status && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.status[0]}
                                    </p>
                                )}
                            </div>

                            <div className="sm:col-span-2">
                                <label
                                    htmlFor="siswa-alamat"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Alamat *
                                </label>

                                <textarea
                                    id="siswa-alamat"
                                    value={form.alamat}
                                    onChange={(e) =>
                                        setData('alamat', e.target.value)
                                    }
                                    rows={3}
                                    required
                                    className={inputClass}
                                />

                                {errors.alamat && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.alamat[0]}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="mt-6 flex justify-end gap-2">
                            <button
                                type="button"
                                onClick={onClose}
                                className="rounded-lg border px-4 py-2 text-sm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                disabled={
                                    saving ||
                                    loadingKelas ||
                                    Boolean(kelasError)
                                }
                                className="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-50"
                            >
                                {saving
                                    ? 'Menyimpan...'
                                    : data
                                        ? 'Simpan Perubahan'
                                        : 'Tambah Siswa'}
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    );
}