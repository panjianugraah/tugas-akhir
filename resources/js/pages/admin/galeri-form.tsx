import axios from 'axios';
import { ImagePlus, X } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import {
    createGaleri,
    updateGaleri,
} from '../../../api/galeri_api';

export type GaleriItem = {
    id: number;
    gambar_url: string | null;
    deskripsi: string | null;
    created_by?: number | null;
    pembuat?: {
        id: number;
        name: string;
    } | null;
};

type Props = {
    open: boolean;
    data: GaleriItem | null;
    onClose: () => void;
    onSaved: () => void | Promise<void>;
};

export default function GaleriForm({
    open,
    data,
    onClose,
    onSaved,
}: Props) {
    const [deskripsi, setDeskripsi] = useState('');
    const [file, setFile] = useState<File | null>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [pesanError, setPesanError] = useState('');

    // Reset form ketika dibuka atau data edit berubah.
    useEffect(() => {
        if (!open) return;

        setDeskripsi(data?.deskripsi ?? '');
        setFile(null);
        setErrors({});
        setPesanError('');
    }, [open, data]);

    // Preview lokal dan pembersihan object URL.
    useEffect(() => {
        if (!open || !file) {
            setPreview(null);
            return;
        }

        const url = URL.createObjectURL(file);
        setPreview(url);

        return () => URL.revokeObjectURL(url);
    }, [open, file]);

    if (!open) return null;

    const gambarPreview = preview ?? data?.gambar_url;

    const pilihGambar = (gambar?: File) => {
        if (!gambar) return;

        const allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!allowed.includes(gambar.type)) {
            setErrors((previous) => ({
                ...previous,
                gambar: ['Format gambar harus JPG, PNG, atau WEBP.'],
            }));
            return;
        }

        if (gambar.size > 2 * 1024 * 1024) {
            setErrors((previous) => ({
                ...previous,
                gambar: ['Ukuran gambar maksimal 2 MB.'],
            }));
            return;
        }

        setFile(gambar);
        setErrors((previous) => ({
            ...previous,
            gambar: [],
        }));
    };

    const simpan = async (event: FormEvent) => {
        event.preventDefault();
        if (saving) return;

        if (!data && !file) {
            setErrors({ gambar: ['Gambar wajib dipilih.'] });
            return;
        }

        setSaving(true);
        setErrors({});
        setPesanError('');

        const payload: {
            deskripsi: string;
            gambar?: File;
        } = { deskripsi };

        if (file) {
            payload.gambar = file;
        }

        try {
            if (data) {
                await updateGaleri(data.id, payload);
            } else {
                await createGaleri(payload);
            }
        } catch (error) {
            if (axios.isAxiosError(error)) {
                if (error.response?.status === 422) {
                    setErrors(error.response.data.errors ?? {});
                } else {
                    setPesanError(
                        error.response?.status === 413
                            ? 'Ukuran file terlalu besar untuk server.'
                            : error.response?.data?.message ??
                            'Gagal menyimpan galeri.'
                    );
                }
            } else {
                setPesanError('Gagal menyimpan galeri.');
            }

            setSaving(false);
            return;
        }

        setSaving(false);
        onClose();
        await onSaved();
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="galeri-form-title"
                className="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-background shadow-xl"
            >
                <div className="flex items-center justify-between border-b p-5">
                    <h2
                        id="galeri-form-title"
                        className="text-lg font-semibold"
                    >
                        {data ? 'Edit Galeri' : 'Tambah Foto Galeri'}
                    </h2>

                    <button
                        type="button"
                        onClick={onClose}
                        disabled={saving}
                        aria-label="Tutup form"
                        className="rounded p-1 hover:bg-muted"
                    >
                        <X className="size-5" />
                    </button>
                </div>

                <form onSubmit={simpan} className="space-y-4 p-5">
                    <div>
                        {gambarPreview ? (
                            <img
                                src={gambarPreview}
                                alt="Preview gambar galeri"
                                className="h-64 w-full rounded-lg bg-muted object-contain"
                            />
                        ) : (
                            <div className="flex h-64 items-center justify-center rounded-lg border-2 border-dashed">
                                <ImagePlus className="size-10 text-muted-foreground" />
                            </div>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="galeri-gambar"
                            className="mb-1 block text-sm font-medium"
                        >
                            {data ? 'Ganti Gambar' : 'Pilih Gambar'}
                        </label>

                        <input
                            id="galeri-gambar"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            disabled={saving}
                            onChange={(event) => {
                                pilihGambar(event.target.files?.[0]);

                                // Memungkinkan file yang sama dipilih ulang.
                                event.target.value = '';
                            }}
                            className="w-full rounded-lg border px-3 py-2 text-sm"
                        />

                        <p className="mt-1 text-xs text-muted-foreground">
                            JPG, PNG, WEBP. Maksimal 2 MB.
                            {data && ' Tidak memilih gambar baru akan mempertahankan gambar lama.'}
                        </p>

                        {file && (
                            <p className="mt-2 break-words text-xs text-blue-700">
                                Gambar baru: {file.name}
                            </p>
                        )}

                        {errors.gambar?.[0] && (
                            <p className="mt-1 text-xs text-red-600">
                                {errors.gambar[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="galeri-deskripsi"
                            className="mb-1 block text-sm font-medium"
                        >
                            Deskripsi Singkat
                        </label>

                        <textarea
                            id="galeri-deskripsi"
                            value={deskripsi}
                            onChange={(event) =>
                                setDeskripsi(event.target.value)
                            }
                            disabled={saving}
                            rows={3}
                            maxLength={255}
                            placeholder="Contoh: Kegiatan upacara Hari Pendidikan Nasional"
                            className="w-full rounded-lg border bg-background px-3 py-2 text-sm"
                        />

                        <p className="mt-1 text-xs text-muted-foreground">
                            {deskripsi.length}/255 karakter
                        </p>

                        {errors.deskripsi?.[0] && (
                            <p className="mt-1 text-xs text-red-600">
                                {errors.deskripsi[0]}
                            </p>
                        )}
                    </div>

                    {pesanError && (
                        <p role="alert" className="text-sm text-red-600">
                            {pesanError}
                        </p>
                    )}

                    <div className="flex justify-end gap-2">
                        <button
                            type="button"
                            onClick={onClose}
                            disabled={saving}
                            className="rounded-lg border px-4 py-2 text-sm"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            disabled={saving}
                            className="rounded-lg bg-blue-700 px-4 py-2 text-sm text-white hover:bg-blue-800 disabled:opacity-50"
                        >
                            {saving ? 'Menyimpan...' : 'Simpan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}