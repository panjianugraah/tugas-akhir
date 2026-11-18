import axios from 'axios';
import { X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import {
    createMataPelajaran,
    updateMataPelajaran,
} from '../../../api/matapelajaran_api';

export type MataPelajaranItem = {
    id: number;
    nama: string;
    materi_count?: number;
    guru_count?: number;
};

type Props = {
    open: boolean;
    data: MataPelajaranItem | null;
    onClose: () => void;
    onSaved: () => void | Promise<void>;
};

export default function MataPelajaranForm({
    open,
    data,
    onClose,
    onSaved,
}: Props) {
    const [nama, setNama] = useState('');
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (!open) return;

        setNama(data?.nama ?? '');
        setErrors({});
        setError('');
    }, [open, data]);

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (saving) return;

        const namaBersih = nama.trim();

        if (!namaBersih) {
            setErrors({
                nama: ['Nama mata pelajaran wajib diisi.'],
            });
            return;
        }

        setSaving(true);
        setErrors({});
        setError('');

        try {
            const payload = { nama: namaBersih };

            if (data) {
                await updateMataPelajaran(data.id, payload);
            } else {
                await createMataPelajaran(payload);
            }
        } catch (err) {
            if (axios.isAxiosError(err)) {
                if (err.response?.status === 422) {
                    setErrors(err.response.data.errors ?? {});
                }

                setError(
                    err.response?.data?.message ??
                        'Gagal menyimpan mata pelajaran.',
                );
            } else {
                setError('Gagal menyimpan mata pelajaran.');
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
                aria-labelledby="mapel-form-title"
                className="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-background p-6 shadow-xl"
            >
                <div className="mb-5 flex items-center justify-between gap-3">
                    <h2
                        id="mapel-form-title"
                        className="text-xl font-semibold"
                    >
                        {data
                            ? 'Edit Mata Pelajaran'
                            : 'Tambah Mata Pelajaran'}
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
                        <label
                            htmlFor="mapel-nama"
                            className="mb-1 block text-sm font-medium"
                        >
                            Nama Mata Pelajaran *
                        </label>

                        <input
                            id="mapel-nama"
                            name="nama"
                            value={nama}
                            onChange={(event) =>
                                setNama(event.target.value)
                            }
                            placeholder="Contoh: Matematika"
                            maxLength={100}
                            required
                            autoFocus
                            aria-invalid={Boolean(errors.nama)}
                            aria-describedby={
                                errors.nama ? 'mapel-nama-error' : undefined
                            }
                            className="w-full rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />

                        {errors.nama && (
                            <p
                                id="mapel-nama-error"
                                className="mt-1 text-sm text-red-600"
                            >
                                {errors.nama[0]}
                            </p>
                        )}

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
                                {saving ? 'Menyimpan...' : 'Simpan'}
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    );
}