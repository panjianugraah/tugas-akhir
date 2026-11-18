import axios from 'axios';
import { Pencil, Trash2, X } from 'lucide-react';
import {
    useCallback,
    useEffect,
    useState,
    type FormEvent,
} from 'react';
import DataTablePage from './data-table-page';
import {
    createPeriodePpdb,
    deletePeriodePpdb,
    getAllPeriodePpdb,
    updatePeriodePpdb,
    type PeriodePpdbPayload,
} from '../../../api/periode_ppdb_api';

type PeriodeItem = {
    id: number;
    nama_periode: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    kuota: number | null;
    pendaftar_count: number;
    diterima_count: number;
};

type FormValues = {
    nama_periode: string;
    tanggal_mulai: string;
    tanggal_selesai: string;
    kuota: string;
};

const EMPTY: FormValues = {
    nama_periode: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    kuota: '',
};

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm';

/**
 * Konversi tanggal API ke input datetime-local.
 * Menggunakan zona waktu lokal browser.
 */
function toInputDate(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '';

    const pad = (number: number) => String(number).padStart(2, '0');

    return [
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`,
        `${pad(date.getHours())}:${pad(date.getMinutes())}`,
    ].join('T');
}

function formatDate(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '-';

    return date.toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function AdminPeriodePpdb() {
    const [rows, setRows] = useState<PeriodeItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState('');

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<PeriodeItem | null>(null);
    const [form, setForm] = useState<FormValues>(EMPTY);

    const [saving, setSaving] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [formError, setFormError] = useState('');
    const [pesan, setPesan] = useState('');

    const load = useCallback(async () => {
        setLoading(true);
        setLoadError('');

        try {
            setRows(await getAllPeriodePpdb());
        } catch {
            setLoadError('Gagal memuat periode PPDB.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        void load();
    }, [load]);

    const bukaTambah = () => {
        setEditing(null);
        setForm({ ...EMPTY });
        setErrors({});
        setFormError('');
        setPesan('');
        setFormOpen(true);
    };

    const bukaEdit = (row: PeriodeItem) => {
        setEditing(row);

        setForm({
            nama_periode: row.nama_periode,
            tanggal_mulai: toInputDate(row.tanggal_mulai),
            tanggal_selesai: toInputDate(row.tanggal_selesai),
            kuota: row.kuota == null ? '' : String(row.kuota),
        });

        setErrors({});
        setFormError('');
        setPesan('');
        setFormOpen(true);
    };

    const ubahField = (name: keyof FormValues, value: string) => {
        setForm((previous) => ({
            ...previous,
            [name]: value,
        }));

        setErrors((previous) => ({
            ...previous,
            [name]: [],
        }));
    };

    const simpan = async (event: FormEvent) => {
        event.preventDefault();

        if (saving) return;

        setSaving(true);
        setErrors({});
        setFormError('');

        // Kirim waktu dengan offset agar zona waktunya tidak ambigu.
        const payload: PeriodePpdbPayload = {
            nama_periode: form.nama_periode.trim(),
            tanggal_mulai: new Date(form.tanggal_mulai).toISOString(),
            tanggal_selesai: new Date(form.tanggal_selesai).toISOString(),
            kuota: form.kuota === '' ? null : Number(form.kuota),
        };

        try {
            if (editing) {
                await updatePeriodePpdb(editing.id, payload);
            } else {
                await createPeriodePpdb(payload);
            }
        } catch (err) {
            if (axios.isAxiosError(err)) {
                if (err.response?.status === 422) {
                    setErrors(err.response.data.errors ?? {});
                } else {
                    setFormError(
                        err.response?.data?.message ??
                        'Gagal menyimpan periode.'
                    );
                }
            } else {
                setFormError('Gagal menyimpan periode.');
            }

            setSaving(false);
            return;
        }

        setSaving(false);
        setFormOpen(false);
        setPesan(
            editing
                ? 'Periode berhasil diperbarui.'
                : 'Periode berhasil ditambahkan.'
        );

        await load();
    };

    const hapus = async (row: PeriodeItem) => {
        if (deletingId !== null) return;

        if (!confirm(`Hapus periode "${row.nama_periode}"?`)) {
            return;
        }

        setDeletingId(row.id);
        setPesan('');

        try {
            await deletePeriodePpdb(row.id);
            setPesan('Periode berhasil dihapus.');
            await load();
        } catch (err) {
            alert(
                axios.isAxiosError(err)
                    ? err.response?.data?.message ??
                      'Gagal menghapus periode.'
                    : 'Gagal menghapus periode.'
            );
        } finally {
            setDeletingId(null);
        }
    };

    const fieldError = (name: string) =>
        errors[name]?.[0] ? (
            <p className="mt-1 text-xs text-red-600">
                {errors[name][0]}
            </p>
        ) : null;

    return (
        <>
            {pesan && (
                <p role="status" className="px-4 pt-4 text-sm text-green-700">
                    {pesan}
                </p>
            )}

            {loadError && (
                <div className="px-4 pt-4">
                    <p role="alert" className="text-sm text-red-600">
                        {loadError}
                    </p>

                    <button
                        onClick={() => void load()}
                        className="mt-2 text-sm text-blue-700 underline"
                    >
                        Coba lagi
                    </button>
                </div>
            )}

            <DataTablePage
                title="Periode PPDB"
                description="Kelola jadwal, kuota informasi, dan jumlah peserta per periode"
                addLabel="Tambah Periode"
                onAdd={bukaTambah}
                rows={rows}
                loading={loading}
                columns={[
                    {
                        key: 'nama_periode',
                        label: 'Nama Periode',
                    },
                    {
                        key: 'tanggal_mulai',
                        label: 'Mulai',
                        render: (row) => formatDate(row.tanggal_mulai),
                    },
                    {
                        key: 'tanggal_selesai',
                        label: 'Selesai',
                        render: (row) => formatDate(row.tanggal_selesai),
                    },
                    {
                        key: 'kuota',
                        label: 'Kuota',
                        render: (row) => row.kuota ?? 'Belum ditentukan',
                    },
                    {
                        key: 'pendaftar_count',
                        label: 'Pendaftar',
                    },
                    {
                        key: 'diterima_count',
                        label: 'Diterima',
                    },
                ]}
                actions={(row: PeriodeItem) => (
                    <>
                        <button
                            onClick={() => bukaEdit(row)}
                            title="Edit Periode"
                            className="rounded p-1.5 text-blue-700 hover:bg-blue-50"
                        >
                            <Pencil className="size-4" />
                        </button>

                        <button
                            onClick={() => hapus(row)}
                            disabled={deletingId !== null}
                            title="Hapus Periode"
                            className="rounded p-1.5 text-red-700 hover:bg-red-50 disabled:opacity-50"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </>
                )}
            />

            {formOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="periode-form-title"
                        className="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-background shadow-xl"
                    >
                        <div className="flex items-center justify-between border-b p-5">
                            <h2
                                id="periode-form-title"
                                className="text-lg font-semibold"
                            >
                                {editing ? 'Edit Periode PPDB' : 'Tambah Periode PPDB'}
                            </h2>

                            <button
                                type="button"
                                disabled={saving}
                                onClick={() => setFormOpen(false)}
                                aria-label="Tutup form"
                                className="rounded p-1 hover:bg-muted"
                            >
                                <X className="size-5" />
                            </button>
                        </div>

                        <form onSubmit={simpan} className="space-y-4 p-5">
                            <fieldset disabled={saving} className="space-y-4">
                                <div>
                                    <label
                                        htmlFor="nama-periode"
                                        className="mb-1 block text-sm font-medium"
                                    >
                                        Nama Periode
                                    </label>

                                    <input
                                        id="nama-periode"
                                        value={form.nama_periode}
                                        onChange={(event) =>
                                            ubahField('nama_periode', event.target.value)
                                        }
                                        required
                                        maxLength={150}
                                        placeholder="2027/2028 - Gelombang 1"
                                        className={inputClass}
                                    />

                                    {fieldError('nama_periode')}
                                </div>

                                <div>
                                    <label
                                        htmlFor="tanggal-mulai"
                                        className="mb-1 block text-sm font-medium"
                                    >
                                        Tanggal dan Jam Mulai
                                    </label>

                                    <input
                                        id="tanggal-mulai"
                                        type="datetime-local"
                                        value={form.tanggal_mulai}
                                        onChange={(event) =>
                                            ubahField('tanggal_mulai', event.target.value)
                                        }
                                        required
                                        className={inputClass}
                                    />

                                    {fieldError('tanggal_mulai')}
                                </div>

                                <div>
                                    <label
                                        htmlFor="tanggal-selesai"
                                        className="mb-1 block text-sm font-medium"
                                    >
                                        Tanggal dan Jam Selesai
                                    </label>

                                    <input
                                        id="tanggal-selesai"
                                        type="datetime-local"
                                        value={form.tanggal_selesai}
                                        onChange={(event) =>
                                            ubahField('tanggal_selesai', event.target.value)
                                        }
                                        required
                                        className={inputClass}
                                    />

                                    {fieldError('tanggal_selesai')}
                                </div>

                                <div>
                                    <label
                                        htmlFor="kuota"
                                        className="mb-1 block text-sm font-medium"
                                    >
                                        Kuota Penerimaan
                                    </label>

                                    <input
                                        id="kuota"
                                        type="number"
                                        min={0}
                                        step={1}
                                        value={form.kuota}
                                        onChange={(event) =>
                                            ubahField('kuota', event.target.value)
                                        }
                                        placeholder="Kosongkan jika belum ditentukan"
                                        className={inputClass}
                                    />

                                    {fieldError('kuota')}

                                    <p className="mt-2 text-xs text-muted-foreground">
                                        Kuota hanya sebagai informasi.
                                        Tidak membatasi pendaftaran atau
                                        penerimaan secara otomatis.
                                    </p>
                                </div>
                            </fieldset>

                            {formError && (
                                <p role="alert" className="text-sm text-red-600">
                                    {formError}
                                </p>
                            )}

                            <div className="flex justify-end gap-2">
                                <button
                                    type="button"
                                    disabled={saving}
                                    onClick={() => setFormOpen(false)}
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
            )}
        </>
    );
}