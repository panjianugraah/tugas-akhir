import { ImagePlus, Loader2, Trash2, X } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent } from 'react';
import { createPengumuman, updatePengumuman } from '../../../api/pengumuman_api';

type Props = {
    open: boolean;
    data?: any | null;      // diisi saat edit, null saat tambah
    created_by?: number | null;
    pembuat?: {
        id: number;
        name: string;
    } | null;
    onClose: () => void;
    onSaved: () => void;    // dipanggil setelah berhasil simpan (untuk reload tabel)
};

const EMPTY = { judul: '', isi: '', berlaku_sampai: '' };
const MAX_SIZE = 2 * 1024 * 1024; // 2 MB
const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-blue-200 focus:outline-none';

export default function PengumumanForm({ open, data, onClose, onSaved }: Props) {
    const [form, setForm] = useState(EMPTY);
    const [file, setFile] = useState<File | null>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [hapusGambar, setHapusGambar] = useState(false);
    const [dragging, setDragging] = useState(false);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [saving, setSaving] = useState<'draft' | 'terbit' | null>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    const isEdit = !!data;

    // Isi ulang form setiap kali modal dibuka
    useEffect(() => {
        if (!open) return;
        setForm(
            data
                ? { judul: data.judul, isi: data.isi, berlaku_sampai: data.berlaku_sampai ?? '' }
                : EMPTY,
        );
        setFile(null);
        setPreview(data?.gambar_url ?? null);
        setHapusGambar(false);
        setErrors({});
    }, [open, data]);

    // Bersihkan URL preview lokal agar tidak memakan memori
    useEffect(() => {
        return () => {
            if (preview?.startsWith('blob:')) URL.revokeObjectURL(preview);
        };
    }, [preview]);

    if (!open) return null;

    const set = (key: string, value: string) => {
        setForm((f) => ({ ...f, [key]: value }));
        setErrors((e) => ({ ...e, [key]: [] }));
    };

    const pilihGambar = (f?: File | null) => {
        if (!f) return;
        if (!ALLOWED.includes(f.type)) {
            setErrors((e) => ({ ...e, gambar: ['Gambar harus berformat jpg, png, atau webp.'] }));
            return;
        }
        if (f.size > MAX_SIZE) {
            setErrors((e) => ({ ...e, gambar: ['Ukuran gambar maksimal 2 MB.'] }));
            return;
        }
        setFile(f);
        setPreview(URL.createObjectURL(f));
        setHapusGambar(false);
        setErrors((e) => ({ ...e, gambar: [] }));
    };

    const buangGambar = () => {
        setFile(null);
        setPreview(null);
        if (data?.gambar_url) setHapusGambar(true); // hapus gambar lama di server
        if (inputRef.current) inputRef.current.value = '';
    };

    const simpan = async (status: 'draft' | 'terbit') => {
        setSaving(status);
        try {
            const payload: any = { ...form, status };
            if (file) payload.gambar = file;
            if (hapusGambar) payload.hapus_gambar = true;

            if (isEdit) {
                await updatePengumuman(data.id, payload);
            } else {
                await createPengumuman(payload);
            }
            onSaved();
            onClose();
        } catch (err: any) {
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors ?? {});
            } else if (err.response?.status === 413) {
                setErrors({ gambar: ['File terlalu besar untuk server.'] });
            } else {
                alert(err.response?.data?.message ?? 'Gagal menyimpan pengumuman.');
            }
        } finally {
            setSaving(null);
        }
    };

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        simpan('terbit');
    };

    const Error = ({ name }: { name: string }) =>
        errors[name]?.[0] ? <p className="mt-1 text-xs text-red-600">{errors[name][0]}</p> : null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={() => !saving && onClose()}>
            <div className="flex max-h-[92vh] w-full max-w-3xl flex-col rounded-xl bg-background shadow-xl" onClick={(e) => e.stopPropagation()}>
                {/* Header */}
                <div className="flex items-center justify-between border-b px-5 py-4">
                    <h2 className="text-lg font-semibold">{isEdit ? 'Edit Pengumuman' : 'Buat Pengumuman'}</h2>
                    <button onClick={onClose} disabled={!!saving} className="rounded p-1 hover:bg-muted">
                        <X className="size-5" />
                    </button>
                </div>

                {/* Body */}
                <form id="form-pengumuman" onSubmit={onSubmit} className="flex flex-col gap-4 overflow-y-auto p-5">
                    {/* Gambar sampul */}
                    <div>
                        <label className="mb-1 block text-sm font-medium">Gambar Sampul</label>

                        {preview ? (
                            <div className="relative overflow-hidden rounded-lg border">
                                <img src={preview} alt="Preview" className="aspect-video w-full object-cover" />
                                <div className="absolute top-2 right-2 flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => inputRef.current?.click()}
                                        className="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium shadow hover:bg-white"
                                    >
                                        Ganti
                                    </button>
                                    <button
                                        type="button"
                                        onClick={buangGambar}
                                        className="rounded-lg bg-white/90 p-1.5 text-red-700 shadow hover:bg-white"
                                        title="Hapus gambar"
                                    >
                                        <Trash2 className="size-4" />
                                    </button>
                                </div>
                                {file && (
                                    <p className="absolute bottom-0 left-0 right-0 truncate bg-black/50 px-3 py-1 text-xs text-white">
                                        {file.name} · {(file.size / 1024).toFixed(0)} KB
                                    </p>
                                )}
                            </div>
                        ) : (
                            <div
                                onClick={() => inputRef.current?.click()}
                                onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
                                onDragLeave={() => setDragging(false)}
                                onDrop={(e) => { e.preventDefault(); setDragging(false); pilihGambar(e.dataTransfer.files?.[0]); }}
                                className={`flex aspect-video cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed text-sm text-muted-foreground transition ${dragging ? 'border-blue-500 bg-blue-50' : 'hover:border-blue-400 hover:bg-muted/40'
                                    } ${errors.gambar?.[0] ? 'border-red-500' : ''}`}
                            >
                                <ImagePlus className="size-8" />
                                <p><span className="font-medium text-blue-700">Klik untuk upload</span> atau seret gambar ke sini</p>
                                <p className="text-xs">JPG, PNG, WEBP · maks. 2 MB</p>
                            </div>
                        )}

                        <input
                            ref={inputRef}
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            className="hidden"
                            onChange={(e) => pilihGambar(e.target.files?.[0])}
                        />
                        <Error name="gambar" />
                    </div>

                    {/* Judul */}
                    <div>
                        <label htmlFor="judul" className="mb-1 block text-sm font-medium">
                            Judul <span className="text-red-600">*</span>
                        </label>
                        <input
                            id="judul"
                            value={form.judul}
                            onChange={(e) => set('judul', e.target.value)}
                            placeholder="contoh: Jadwal Penilaian Tengah Semester Ganjil"
                            className={`${inputClass} ${errors.judul?.[0] ? 'border-red-500' : ''}`}
                        />
                        <Error name="judul" />
                    </div>

                    {/* Target & berlaku */}
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <label htmlFor="berlaku_sampai" className="mb-1 block text-sm font-medium">Berlaku Sampai</label>
                            <input
                                id="berlaku_sampai"
                                type="date"
                                value={form.berlaku_sampai}
                                onChange={(e) => set('berlaku_sampai', e.target.value)}
                                className={inputClass}
                            />
                            <p className="mt-1 text-xs text-muted-foreground">Kosongkan jika berlaku tanpa batas.</p>
                            <Error name="berlaku_sampai" />
                        </div>
                    </div>

                    {/* Isi */}
                    <div>
                        <label htmlFor="isi" className="mb-1 block text-sm font-medium">
                            Isi Pengumuman <span className="text-red-600">*</span>
                        </label>
                        <textarea
                            id="isi"
                            rows={8}
                            value={form.isi}
                            onChange={(e) => set('isi', e.target.value)}
                            placeholder="Tulis isi pengumuman di sini..."
                            className={`${inputClass} ${errors.isi?.[0] ? 'border-red-500' : ''}`}
                        />
                        <Error name="isi" />
                    </div>
                </form>

                {/* Footer */}
                <div className="flex justify-end gap-2 border-t px-5 py-4">
                    <button type="button" onClick={onClose} disabled={!!saving} className="rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                        Batal
                    </button>
                    <button
                        type="button"
                        onClick={() => simpan('draft')}
                        disabled={!!saving}
                        className="inline-flex items-center gap-2 rounded-lg border border-blue-700 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50 disabled:opacity-60"
                    >
                        {saving === 'draft' && <Loader2 className="size-4 animate-spin" />}
                        Simpan Draft
                    </button>
                    <button
                        type="submit"
                        form="form-pengumuman"
                        disabled={!!saving}
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 disabled:opacity-60"
                    >
                        {saving === 'terbit' && <Loader2 className="size-4 animate-spin" />}
                        Terbitkan
                    </button>
                </div>
            </div>
        </div>
    );
}