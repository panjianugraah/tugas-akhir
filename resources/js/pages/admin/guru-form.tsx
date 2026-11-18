import axios from 'axios';
import { X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { createGuru, updateGuru } from '../../../api/guru_api';

export type GuruItem = {
    id: number;
    nip: string | null;
    nama_lengkap: string;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    mata_pelajaran_id: number | null;
    mapel?: {
        id: number;
        nama: string;
    } | null;
    no_hp: string;
    email: string | null;
    alamat: string;
    status: string;
};

type FormData = {
    nip: string;
    nama_lengkap: string;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    mata_pelajaran_id: string;
    no_hp: string;
    email: string;
    alamat: string;
    status: string;
};

type Props = {
    open: boolean;
    data: GuruItem | null;
    onClose: () => void;
    onSaved: () => void | Promise<void>;
};

const initialData: FormData = {
    nip: '',
    nama_lengkap: '',
    jenis_kelamin: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    mata_pelajaran_id: '',
    no_hp: '',
    email: '',
    alamat: '',
    status: 'aktif',
};

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200';

// Field input biasa. Dropdown dan alamat ditulis terpisah di bawah.
const inputFields = [
    {
        name: 'nip',
        label: 'NIP',
        type: 'text',
        required: false,
        maxLength: 18,
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

export default function GuruForm({
    open,
    data,
    onClose,
    onSaved,
}: Props) {
    const [form, setForm] = useState<FormData>(initialData);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);
    const [mapel, setMapel] = useState<MapelOption[]>([]);
    const [loadingMapel, setLoadingMapel] = useState(false);
    const [mapelError, setMapelError] = useState('');

    useEffect(() => {
        if (!open) return;

        setErrors({});
        setError('');

        if (data) {
            setForm({
                nip: data.nip ?? '',
                nama_lengkap: data.nama_lengkap ?? '',
                jenis_kelamin: data.jenis_kelamin ?? '',
                tempat_lahir: data.tempat_lahir ?? '',
                tanggal_lahir: data.tanggal_lahir?.slice(0, 10) ?? '',
                mata_pelajaran_id:
                    data.mata_pelajaran_id != null
                        ? String(data.mata_pelajaran_id)
                        : '',
                no_hp: data.no_hp ?? '',
                email: data.email ?? '',
                alamat: data.alamat ?? '',
                status: data.status ?? 'aktif',
            });
        } else {
            setForm({ ...initialData });
        }
    }, [open, data]);

    useEffect(() => {
        if (!open) return;

        let active = true;

        const loadMapel = async () => {
            setLoadingMapel(true);
            setMapelError('');

            try {
                const response = await axios.get('/api/mata-pelajaran');

                const items = response.data.data ?? response.data;

                if (!Array.isArray(items)) {
                    throw new Error('Format data mata pelajaran tidak sesuai.');
                }

                if (active) {
                    setMapel(items);
                }
            } catch {
                if (active) {
                    setMapelError('Gagal memuat mata pelajaran.');
                }
            } finally {
                if (active) {
                    setLoadingMapel(false);
                }
            }
        };

        void loadMapel();

        return () => {
            active = false;
        };
    }, [open]);

    type MapelOption = {
        id: number;
        nama: string;
    };

    const setData = (key: keyof FormData, value: string) => {
        setForm((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (
            saving ||
            loadingMapel ||
            Boolean(mapelError) ||
            mapel.length === 0
        ) {
            return;
        }

        setSaving(true);
        setErrors({});
        setError('');

        const payload = {
            ...form,
            nama_lengkap: form.nama_lengkap.trim(),
            nip: form.nip.trim() || null,
            email: form.email.trim() || null,
            mata_pelajaran_id: Number(form.mata_pelajaran_id),
        };

        try {
            if (data) {
                await updateGuru(data.id, payload);
            } else {
                await createGuru(payload);
            }
        } catch (err) {
            if (axios.isAxiosError(err)) {
                if (err.response?.status === 422) {
                    setErrors(err.response.data.errors ?? {});
                }

                setError(
                    err.response?.data?.message ??
                    'Gagal menyimpan data guru.',
                );
            } else {
                setError('Gagal menyimpan data guru.');
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
                aria-labelledby="guru-form-title"
                className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-background p-6 shadow-xl"
            >
                <div className="mb-5 flex items-center justify-between">
                    <h2
                        id="guru-form-title"
                        className="text-xl font-semibold"
                    >
                        {data ? 'Edit Guru' : 'Tambah Guru'}
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
                                        htmlFor={`guru-${field.name}`}
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
                                        id={`guru-${field.name}`}
                                        type={field.type}
                                        value={form[field.name]}
                                        onChange={(e) =>
                                            setData(field.name, e.target.value)
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
                                    htmlFor="guru-mata-pelajaran"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Mata Pelajaran *
                                </label>

                                <select
                                    id="guru-mata-pelajaran"
                                    value={form.mata_pelajaran_id}
                                    onChange={(e) =>
                                        setData('mata_pelajaran_id', e.target.value)
                                    }
                                    disabled={
                                        saving ||
                                        loadingMapel ||
                                        Boolean(mapelError)
                                    }
                                    required
                                    className={inputClass}
                                >
                                    <option value="">
                                        {loadingMapel
                                            ? 'Memuat mata pelajaran...'
                                            : '-- Pilih Mata Pelajaran --'}
                                    </option>

                                    {mapel.map((item) => (
                                        <option key={item.id} value={String(item.id)}>
                                            {item.nama}
                                        </option>
                                    ))}
                                </select>

                                {errors.mata_pelajaran_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.mata_pelajaran_id[0]}
                                    </p>
                                )}

                                {mapelError && (
                                    <p role="alert" className="mt-1 text-sm text-red-600">
                                        {mapelError} Tutup dan buka kembali form untuk mencoba lagi.
                                    </p>
                                )}

                                {!loadingMapel && !mapelError && mapel.length === 0 && (
                                    <p className="mt-1 text-sm text-amber-700">
                                        Belum ada mata pelajaran. Tambahkan data mata pelajaran
                                        terlebih dahulu.
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="guru-jenis-kelamin"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Jenis Kelamin *
                                </label>

                                <select
                                    id="guru-jenis-kelamin"
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
                                    htmlFor="guru-status"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Status *
                                </label>

                                <select
                                    id="guru-status"
                                    value={form.status}
                                    onChange={(e) =>
                                        setData('status', e.target.value)
                                    }
                                    required
                                    className={inputClass}
                                >
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>

                                {errors.status && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.status[0]}
                                    </p>
                                )}
                            </div>

                            <div className="sm:col-span-2">
                                <label
                                    htmlFor="guru-alamat"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Alamat *
                                </label>

                                <textarea
                                    id="guru-alamat"
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
                                disabled={saving}
                                className="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-50"
                            >
                                {saving
                                    ? 'Menyimpan...'
                                    : data
                                        ? 'Simpan Perubahan'
                                        : 'Tambah Guru'}
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    );
}