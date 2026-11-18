import { Check, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import DataTablePage, { StatusBadge } from '../admin/data-table-page';
import { deletePpdb, getAllPpdb, updateStatusPpdb } from '../../../api/ppdb_api';

export default function AdminPpdb() {
    const [rows, setRows] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [status, setStatus] = useState('');

    const load = async () => {
        setLoading(true);
        try {
            setRows(await getAllPpdb(status ? { status } : undefined));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { load(); }, [status]);

    const ubahStatus = async (id: number, s: string) => {
        try {
            await updateStatusPpdb(id, s);
            load();
        } catch (error: any) {
            alert(error.response?.data?.message ?? 'Gagal mengubah status.');
        }
    };

    const hapus = async (id: number) => {
        if (!confirm('Hapus data pendaftar ini?')) return;
        await deletePpdb(id);
        load();
    };

    return (
        <DataTablePage
            title="Data Pendaftar PPDB"
            description="Verifikasi calon peserta didik baru"
            rows={rows}
            loading={loading}
            toolbar={
                <select value={status} onChange={(e) => setStatus(e.target.value)} className="rounded-lg border bg-background px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            }
            columns={[
                { key: 'nisn', label: 'NISN' },
                { key: 'nama_lengkap', label: 'Nama' },
                { key: 'asal_sekolah', label: 'Asal Sekolah' },
                { key: 'no_hp', label: 'No. HP' },
                { key: 'status', label: 'Status', render: (r) => <StatusBadge value={r.status} /> },
            ]}
            actions={(r) => (
                <>
                    <button onClick={() => ubahStatus(r.id, 'diterima')} className="rounded p-1.5 text-green-700 hover:bg-green-50" title="Terima"><Check className="size-4" /></button>
                    <button onClick={() => ubahStatus(r.id, 'ditolak')} className="rounded p-1.5 text-orange-600 hover:bg-orange-50" title="Tolak"><X className="size-4" /></button>
                    <button onClick={() => hapus(r.id)} className="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus"><Trash2 className="size-4" /></button>
                </>
            )}
        />
    );
}