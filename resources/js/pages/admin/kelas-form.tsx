import axios from 'axios';
import { useEffect, useState, type FormEvent } from 'react';
import { X } from 'lucide-react';
import { createKelas, updateKelas } from '../../../api/kelas_api';
import { getAllGuru } from '../../../api/guru_api';

export type KelasItem = {
    id: number;
    nama_kelas: string;
    tingkat: number;
    tahun_ajaran: string;
    kapasitas: number;
    wali_kelas_id: number | null;
    wali_kelas?: {
        id: number;
        nama_lengkap: string;
    } | null;
};

type GuruOption = {
    id: number;
    nama_lengkap: string;
};

type FormData = {
    nama_kelas: string;
    tingkat: string;
    tahun_ajaran: string;
    kapasitas: string;
    wali_kelas_id: string;
};

type Props = {
    open: boolean;
    data: KelasItem | null;
    onClose: () => void;
    onSaved: () => void | Promise<void>;
};

const initialData: FormData = {
    nama_kelas: '',
    tingkat: '7',
    tahun_ajaran: '',
    kapasitas: '32',
    wali_kelas_id: '',
};

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200';

export default function KelasForm({
    open,
    data,
    onClose,
    onSaved,
}: Props) {
    const [form, setForm] = useState<FormData>(initialData);
    const [guru, setGuru] = useState<GuruOption[]>([]);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);
    const [loadingGuru, setLoadingGuru] = useState(false);
    const [guruError, setGuruError] = useState('');

    useEffect(() => {
        if (!open) return;

        setErrors({});
        setError('');

        setForm(
            data
                ? {
                      nama_kelas: data.nama_kelas,
                      tingkat: String(data.tingkat),
                      tahun_ajaran: data.tahun_ajaran,
                      kapasitas: String(data.kapasitas),
                      wali_kelas_id:
                          data.wali_kelas_id !== null
                              ? String(data.wali_kelas_id)
                              : '',
                  }
                : { ...initialData },
        );
    }, [open, data]);

    useEffect(() => {
        if (!open) return;

        let active = true;

        const loadGuru = async () => {
            setLoadingGuru(true);
            setGuruError('');

            try {
                const result = await getAllGuru();

                if (!Array.isArray(result)) {
                    throw new Error('Format data guru tidak sesuai.');
                }

                if (active) {
                    setGuru(result);
                }
            } catch {
                if (active) {
                    setGuruError('Gagal memuat pilihan wali kelas.');
                }
            } finally {
                if (active) {
                    setLoadingGuru(false);
                }
            }
        };

        void loadGuru();

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

        if (saving || loadingGuru || guruError) return;

        setSaving(true);
        setErrors({});
        setError('');

        const payload = {
            nama_kelas: form.nama_kelas.trim(),
            tingkat: Number(form.tingkat),
            tahun_ajaran: form.tahun_ajaran.trim(),
            kapasitas: Number(form.kapasitas),
            wali_kelas_id: form.wali_kelas_id
                ? Number(form.wali_kelas_id)
                : null,
        };

        try {
            if (data) {
                await updateKelas(data.id, payload);
            } else {
                await createKelas(payload);
            }
        } catch (err) {
            if (axios.isAxiosError(err)) {
                if (err.response?.status === 422) {
                    setErrors(err.response.data.errors ?? {});
                }

                setError(
                    err.response?.data?.message ??
                        'Gagal menyimpan data kelas.',
                );
            } else {
                setError('Gagal menyimpan data kelas.');
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
                aria-labelledby="kelas-form-title"
                className="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-background p-6 shadow-xl"
            >
                <div className="mb-5 flex items-center justify-between">
                    <h2
                        id="kelas-form-title"
                        className="text-xl font-semibold"
                    >
                        {data ? 'Edit Kelas' : 'Tambah Kelas'}
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
                    <fieldset disabled={saving} className="space-y-4">
                        <div>
                            <label
                                htmlFor="nama-kelas"
                                className="mb-1 block text-sm font-medium"
                            >
                                Nama Kelas
                            </label>

                            <input
                                id="nama-kelas"
                                value={form.nama_kelas}
                                onChange={(e) =>
                                    setData('nama_kelas', e.target.value)
                                }
                                placeholder="Contoh: VII A"
                                maxLength={50}
                                required
                                className={inputClass}
                            />

                            {errors.nama_kelas && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.nama_kelas[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="tingkat"
                                className="mb-1 block text-sm font-medium"
                            >
                                Tingkat
                            </label>

                            <select
                                id="tingkat"
                                value={form.tingkat}
                                onChange={(e) =>
                                    setData('tingkat', e.target.value)
                                }
                                required
                                className={inputClass}
                            >
                                <option value="7">VII</option>
                                <option value="8">VIII</option>
                                <option value="9">IX</option>
                            </select>

                            {errors.tingkat && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.tingkat[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="tahun-ajaran"
                                className="mb-1 block text-sm font-medium"
                            >
                                Tahun Ajaran
                            </label>

                            <input
                                id="tahun-ajaran"
                                value={form.tahun_ajaran}
                                onChange={(e) =>
                                    setData('tahun_ajaran', e.target.value)
                                }
                                placeholder="2027/2028"
                                maxLength={9}
                                required
                                className={inputClass}
                            />

                            {errors.tahun_ajaran && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.tahun_ajaran[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="kapasitas"
                                className="mb-1 block text-sm font-medium"
                            >
                                Kapasitas
                            </label>

                            <input
                                id="kapasitas"
                                type="number"
                                min={1}
                                value={form.kapasitas}
                                onChange={(e) =>
                                    setData('kapasitas', e.target.value)
                                }
                                required
                                className={inputClass}
                            />

                            {errors.kapasitas && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.kapasitas[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="wali-kelas"
                                className="mb-1 block text-sm font-medium"
                            >
                                Wali Kelas
                            </label>

                            <select
                                id="wali-kelas"
                                value={form.wali_kelas_id}
                                onChange={(e) =>
                                    setData('wali_kelas_id', e.target.value)
                                }
                                disabled={saving || loadingGuru}
                                className={inputClass}
                            >
                                <option value="">
                                    {loadingGuru
                                        ? 'Memuat guru...'
                                        : 'Belum ditentukan'}
                                </option>

                                {guru.map((item) => (
                                    <option key={item.id} value={item.id}>
                                        {item.nama_lengkap}
                                    </option>
                                ))}
                            </select>

                            {guruError && (
                                <p role="alert" className="mt-1 text-sm text-red-600">
                                    {guruError} Tutup dan buka kembali form
                                    untuk mencoba lagi.
                                </p>
                            )}

                            {errors.wali_kelas_id && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.wali_kelas_id[0]}
                                </p>
                            )}
                        </div>

                        <div className="flex justify-end gap-2 pt-4">
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
                                    loadingGuru ||
                                    Boolean(guruError)
                                }
                                className="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-50"
                            >
                                {saving
                                    ? 'Menyimpan...'
                                    : data
                                      ? 'Simpan Perubahan'
                                      : 'Tambah Kelas'}
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    );
}