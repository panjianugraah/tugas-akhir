import axios from 'axios';
import { Pencil, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import DataTablePage from './data-table-page';
import MataPelajaranForm, {
    type MataPelajaranItem,
} from './mata-pelajaran-form';
import {
    deleteMataPelajaran,
    getAllMataPelajaran,
} from '../../../api/matapelajaran_api';

export default function AdminMataPelajaran() {
    const [rows, setRows] = useState<MataPelajaranItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] =
        useState<MataPelajaranItem | null>(null);

    const [deletingId, setDeletingId] =
        useState<number | null>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');

        try {
            const data = await getAllMataPelajaran();

            if (!Array.isArray(data)) {
                throw new Error('Format respons mata pelajaran tidak sesuai.');
            }

            setRows(data);
        } catch (err) {
            setError(
                axios.isAxiosError(err)
                    ? err.response?.data?.message ??
                          'Gagal memuat mata pelajaran.'
                    : 'Gagal memuat mata pelajaran.',
            );
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        void load();
    }, [load]);

    const bukaTambah = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const bukaEdit = (row: MataPelajaranItem) => {
        setEditing(row);
        setFormOpen(true);
    };

    const hapus = async (row: MataPelajaranItem) => {
        if (deletingId !== null) return;

        if (!confirm(`Hapus mata pelajaran "${row.nama}"?`)) {
            return;
        }

        setDeletingId(row.id);

        try {
            await deleteMataPelajaran(row.id);
            await load();
        } catch (err) {
            alert(
                axios.isAxiosError(err)
                    ? err.response?.data?.message ??
                          'Gagal menghapus mata pelajaran.'
                    : 'Gagal menghapus mata pelajaran.',
            );
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <>
            {error && (
                <div className="px-4 pt-4 md:px-6">
                    <p role="alert" className="text-sm text-red-600">
                        {error}
                    </p>

                    <button
                        type="button"
                        onClick={() => void load()}
                        className="mt-2 text-sm text-blue-700 underline"
                    >
                        Coba lagi
                    </button>
                </div>
            )}

            <DataTablePage
                title="Mata Pelajaran"
                description="Kelola mata pelajaran untuk data guru dan materi repository."
                addLabel="Tambah Mata Pelajaran"
                onAdd={bukaTambah}
                rows={rows}
                loading={loading}
                columns={[
                    {
                        key: 'nama',
                        label: 'Nama Mata Pelajaran',
                    },
                    {
                        key: 'guru_count',
                        label: 'Jumlah Guru',
                        render: (row: MataPelajaranItem) =>
                            row.guru_count ?? '-',
                    },
                    {
                        key: 'materi_count',
                        label: 'Jumlah Materi',
                        render: (row: MataPelajaranItem) =>
                            row.materi_count ?? '-',
                    },
                ]}
                actions={(row: MataPelajaranItem) => (
                    <>
                        <button
                            type="button"
                            onClick={() => bukaEdit(row)}
                            title="Edit"
                            className="rounded p-1.5 text-blue-700 hover:bg-blue-50"
                        >
                            <Pencil className="size-4" />
                        </button>

                        <button
                            type="button"
                            onClick={() => void hapus(row)}
                            disabled={deletingId !== null}
                            title="Hapus"
                            className="rounded p-1.5 text-red-700 hover:bg-red-50 disabled:opacity-50"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </>
                )}
            />

            <MataPelajaranForm
                open={formOpen}
                data={editing}
                onClose={() => setFormOpen(false)}
                onSaved={load}
            />
        </>
    );
}