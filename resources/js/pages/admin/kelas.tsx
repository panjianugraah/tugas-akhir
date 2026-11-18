import axios from 'axios';
import { Pencil, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import DataTablePage from './data-table-page';
import KelasForm, { type KelasItem } from './kelas-form';
import { deleteKelas, getAllKelas } from '../../../api/kelas_api';

export default function AdminKelas() {
    const [rows, setRows] = useState<KelasItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<KelasItem | null>(null);
    const [deletingId, setDeletingId] = useState<number | null>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');

        try {
            setRows(await getAllKelas());
        } catch {
            setError('Gagal memuat data kelas.');
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

    const bukaEdit = (row: KelasItem) => {
        setEditing(row);
        setFormOpen(true);
    };

    const hapus = async (id: number) => {
        if (deletingId !== null) return;
        if (!confirm('Hapus data kelas ini?')) return;

        setDeletingId(id);

        try {
            await deleteKelas(id);
            await load();
        } catch (err) {
            alert(
                axios.isAxiosError(err)
                    ? err.response?.data?.message ??
                          'Gagal menghapus kelas.'
                    : 'Gagal menghapus kelas.',
            );
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <>
            {error && (
                <p
                    role="alert"
                    className="px-4 pt-4 text-sm text-red-600"
                >
                    {error}
                </p>
            )}

            <DataTablePage
                title="Data Kelas"
                description="Kelola kelas dan wali kelas sekolah"
                addLabel="Tambah Kelas"
                onAdd={bukaTambah}
                rows={rows}
                loading={loading}
                columns={[
                    { key: 'nama_kelas', label: 'Nama Kelas' },
                    { key: 'tingkat', label: 'Tingkat' },
                    { key: 'tahun_ajaran', label: 'Tahun Ajaran' },
                    { key: 'kapasitas', label: 'Kapasitas' },
                    {
                        key: 'wali_kelas',
                        label: 'Wali Kelas',
                        render: (row) =>
                            row.wali_kelas?.nama_lengkap ??
                            'Belum ditentukan',
                    },
                ]}
                actions={(row: KelasItem) => (
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
                            onClick={() => void hapus(row.id)}
                            disabled={deletingId !== null}
                            title="Hapus"
                            className="rounded p-1.5 text-red-700 hover:bg-red-50 disabled:opacity-50"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </>
                )}
            />

            <KelasForm
                open={formOpen}
                data={editing}
                onClose={() => setFormOpen(false)}
                onSaved={load}
            />
        </>
    );
}