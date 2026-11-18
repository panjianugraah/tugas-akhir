import axios from 'axios';
import { useEffect, useState, type FormEvent } from 'react';
import {
    createRepository,
    updateRepository,
} from '../../../api/repository_api';
import { getAllMataPelajaran } from '../../../api/matapelajaran_api';

type Props = {
    open: boolean;
    data?: any | null;
    created_by?: number | null;
    pembuat?: {
        id: number;
        name: string;
    } | null;
    onClose: () => void;
    onSaved: () => void | Promise<void>;
};

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm';

export default function RepositoryForm({
    open,
    data,
    onClose,
    onSaved,
}: Props) {
    const [mapel, setMapel] = useState<{ id: number; nama: string }[]>([]);
    const [mapelId, setMapelId] = useState('');
    const [judul, setJudul] = useState('');
    const [deskripsi, setDeskripsi] = useState('');
    const [file, setFile] = useState<File | null>(null);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const [errors, setErrors] = useState<Record<string, string[]>>({});

    useEffect(() => {
        if (!open) return;

        setMapelId(String(data?.mata_pelajaran_id ?? ''));
        setJudul(data?.judul ?? '');
        setDeskripsi(data?.deskripsi ?? '');
        setFile(null);
        setErrors({});
        setError('');
        setMapel([]);

        let aktif = true;

        getAllMataPelajaran()
            .then((items) => {
                if (aktif) setMapel(items);
            })
            .catch(() => {
                if (aktif) setError('Gagal memuat mata pelajaran.');
            });

        return () => {
            aktif = false;
        };
    }, [open, data]);

    if (!open) return null;

    const simpan = async (event: FormEvent) => {
        event.preventDefault();
        if (saving) return;

        setSaving(true);
        setError('');
        setErrors({});

        const payload: any = {
            mata_pelajaran_id: mapelId,
            judul,
            deskripsi,
        };

        if (file) payload.file = file;

        try {
            if (data) {
                await updateRepository(data.id, payload);
            } else {
                await createRepository(payload);
            }
        } catch (err) {
            if (axios.isAxiosError(err) && err.response?.status === 422) {
                setErrors(err.response.data.errors ?? {});
            } else {
                setError('Gagal menyimpan materi.');
            }

            setSaving(false);
            return;
        }

        setSaving(false);
        onClose();
        await onSaved();
    };

    const pesan = (name: string) =>
        errors[name]?.[0] ? (
            <p className="mt-1 text-xs text-red-600">
                {errors[name][0]}
            </p>
        ) : null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="materi-form-title"
                className="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-background p-5 shadow-xl"
            >
                <h2 id="materi-form-title" className="mb-4 text-lg font-semibold">
                    {data ? 'Edit Materi' : 'Tambah Materi'}
                </h2>

                <form onSubmit={simpan} className="space-y-4">
                    <fieldset disabled={saving} className="space-y-4">
                        <div>
                            <label htmlFor="materi-mapel" className="mb-1 block text-sm">
                                Mata Pelajaran
                            </label>
                            <select
                                id="materi-mapel"
                                value={mapelId}
                                onChange={(event) => setMapelId(event.target.value)}
                                required
                                className={inputClass}
                            >
                                <option value="">Pilih mata pelajaran</option>
                                {mapel.map((item) => (
                                    <option key={item.id} value={item.id}>
                                        {item.nama}
                                    </option>
                                ))}
                            </select>
                            {pesan('mata_pelajaran_id')}
                        </div>

                        <div>
                            <label htmlFor="materi-judul" className="mb-1 block text-sm">
                                Judul
                            </label>
                            <input
                                id="materi-judul"
                                value={judul}
                                onChange={(event) => setJudul(event.target.value)}
                                required
                                maxLength={255}
                                className={inputClass}
                            />
                            {pesan('judul')}
                        </div>

                        <div>
                            <label htmlFor="materi-deskripsi" className="mb-1 block text-sm">
                                Deskripsi
                            </label>
                            <textarea
                                id="materi-deskripsi"
                                value={deskripsi}
                                onChange={(event) => setDeskripsi(event.target.value)}
                                rows={3}
                                maxLength={5000}
                                className={inputClass}
                            />
                            {pesan('deskripsi')}
                        </div>

                        <div>
                            <label htmlFor="materi-file" className="mb-1 block text-sm">
                                File Materi
                            </label>

                            {data?.nama_asli && (
                                <p className="mb-2 break-words text-xs text-muted-foreground">
                                    File saat ini: {data.nama_asli}
                                </p>
                            )}

                            <input
                                id="materi-file"
                                type="file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip"
                                onChange={(event) =>
                                    setFile(event.target.files?.[0] ?? null)
                                }
                                className={inputClass}
                            />
                            {pesan('file')}
                        </div>

                        <p className="text-xs text-muted-foreground">
                            Isi minimal file atau tautan video. File maksimal
                            10 MB. Saat edit, file lama tetap disimpan jika
                            tidak memilih pengganti.
                        </p>
                    </fieldset>

                    {error && (
                        <p role="alert" className="text-sm text-red-600">
                            {error}
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
                            disabled={saving}
                            className="rounded-lg bg-blue-700 px-4 py-2 text-sm text-white disabled:opacity-50"
                        >
                            {saving ? 'Menyimpan...' : 'Simpan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}