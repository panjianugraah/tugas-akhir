import { Power, Trash2, Pencil } from 'lucide-react';
import { useEffect, useState } from 'react';
import DataTablePage, { StatusBadge } from '../admin/data-table-page';
import { deleteGuru, getAllGuru, updateStatusGuru } from '../../../api/guru_api';
import GuruForm, { type GuruItem } from './guru-form';

export default function AdminGuru() {
    const [rows, setRows] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [status, setStatus] = useState('');
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<GuruItem | null>(null);

    const bukaTambah = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const bukaEdit = (row: GuruItem) => {
        setEditing(row);
        setFormOpen(true);
    };

    const load = async () => {
        setLoading(true);
        try {
            setRows(await getAllGuru(status ? { status } : undefined));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { load(); }, [status]);

    const ubahStatus = async (id: number, s: string) => {
        try {
            await updateStatusGuru(id, s);
            load();
        } catch (error: any) {
            alert(error.response?.data?.message ?? 'Gagal mengubah status.');
        }
    };

    const hapus = async (id: number) => {
        if (!confirm('Hapus data guru ini?')) return;
        await deleteGuru(id);
        load();
    };

    return (
        <>
            <DataTablePage
                title="Data Guru"
                description="Kelola data guru dan tenaga pengajar"
                rows={rows}
                loading={loading}
                addLabel="Tambah Guru"
                onAdd={bukaTambah}
                toolbar={
                    <select value={status} onChange={(e) => setStatus(e.target.value)} className="rounded-lg border bg-background px-3 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                }
                columns={[
                    { key: 'nip', label: 'NIP', render: (r) => r.nip ?? '-' },
                    { key: 'nama_lengkap', label: 'Nama' },
                    { key: 'jenis_kelamin', label: 'L/P' },
                    { key: 'mapel', label: 'Mata Pelajaran', render: (row: GuruItem) => row.mapel?.nama ?? 'Belum ditentukan' },
                    { key: 'no_hp', label: 'No. HP' },
                    { key: 'status', label: 'Status', render: (r) => <StatusBadge value={r.status} /> },
                ]}
                actions={(r) => (
                    <>
                        <button
                            type="button"
                            onClick={() => bukaEdit(r)}
                            title="Edit"
                            className="rounded p-1.5 text-blue-700 hover:bg-blue-50"
                        >
                            <Pencil className="size-4" />
                        </button>

                        <button onClick={() => hapus(r.id)} className="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus"><Trash2 className="size-4" /></button>
                    </>
                )}
            />
            <GuruForm
                open={formOpen}
                data={editing}
                onClose={() => setFormOpen(false)}
                onSaved={load}
            />
        </>
    );
}