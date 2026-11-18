import { Eye, EyeOff, ImageOff, Pencil, Trash2 } from 'lucide-react'; // ✅ tambah Pencil
import { useEffect, useState } from 'react';
import DataTablePage, { StatusBadge } from '../admin/data-table-page';
import { deletePengumuman, getAllPengumuman, updateStatusPengumuman } from '../../../api/pengumuman_api';
import PengumumanForm from './pengumuman-form';

const selectClass = 'rounded-lg border bg-background px-3 py-2 text-sm';

export default function Pengumuman() {
    const [rows, setRows] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [status, setStatus] = useState('');
    const [target, setTarget] = useState('');

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<any | null>(null);

    const load = async () => {
        setLoading(true);
        try {
            const params: any = {};
            if (status) params.status = status;
            if (target) params.target = target;
            setRows(await getAllPengumuman(params));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { load(); }, [status, target]);


    const bukaTambah = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const bukaEdit = (row: any) => {
        setEditing(row);
        setFormOpen(true);
    };

    const ubahStatus = async (id: number, s: string) => {
        try {
            await updateStatusPengumuman(id, s);
            load();
        } catch (error: any) {
            alert(error.response?.data?.message ?? 'Gagal mengubah status.');
        }
    };

    const hapus = async (id: number) => {
        if (!confirm('Hapus pengumuman ini?')) return;
        await deletePengumuman(id);
        load();
    };

    return (
        <>
            <DataTablePage
                title="Pengumuman"
                description="Kelola pengumuman untuk siswa, orang tua, dan umum"
                addLabel="Buat Pengumuman"
                onAdd={bukaTambah}
                rows={rows}
                loading={loading}
                toolbar={
                    <>
                        <select value={target} onChange={(e) => setTarget(e.target.value)} className={selectClass}>
                            <option value="">Semua Target</option>
                            <option value="Siswa">Siswa</option>
                            <option value="Guru">Guru</option>
                            <option value="Orang Tua">Orang Tua</option>
                            <option value="Umum">Umum</option>
                        </select>
                        <select value={status} onChange={(e) => setStatus(e.target.value)} className={selectClass}>
                            <option value="">Semua Status</option>
                            <option value="terbit">Terbit</option>
                            <option value="draft">Draft</option>
                        </select>
                    </>
                }
                columns={[
                    {
                        key: 'gambar',
                        label: 'Gambar',
                        render: (r) => r.gambar_url
                            ? <img src={r.gambar_url} alt={r.judul} className="h-12 w-20 rounded object-cover" />
                            : <div className="flex h-12 w-20 items-center justify-center rounded bg-muted"><ImageOff className="size-4 text-muted-foreground" /></div>,
                    },
                    { key: 'judul', label: 'Judul' },
                    { key: 'berlaku_sampai', label: 'Berlaku Sampai', render: (r) => r.berlaku_sampai ?? 'Tanpa batas' },
                    { key: 'status', label: 'Status', render: (r) => <StatusBadge value={r.status} /> },
                    { key: 'pembuat', label: 'Dibuat Oleh', render: (row) => row.pembuat?.name ?? 'Tidak tercatat' },
                ]}
                actions={(r) => (
                    <>
                        <button onClick={() => bukaEdit(r)} className="rounded p-1.5 text-blue-700 hover:bg-blue-50" title="Edit"><Pencil className="size-4" /></button>
                        {r.status === 'draft' ? (
                            <button onClick={() => ubahStatus(r.id, 'terbit')} className="rounded p-1.5 text-green-700 hover:bg-green-50" title="Terbitkan"><Eye className="size-4" /></button>
                        ) : (
                            <button onClick={() => ubahStatus(r.id, 'draft')} className="rounded p-1.5 text-orange-600 hover:bg-orange-50" title="Jadikan Draft"><EyeOff className="size-4" /></button>
                        )}
                        <button onClick={() => hapus(r.id)} className="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus"><Trash2 className="size-4" /></button>
                    </>
                )}
            />

            <PengumumanForm
                open={formOpen}
                data={editing}
                onClose={() => setFormOpen(false)}
                onSaved={load}
            />
        </> /* ✅ tutup fragment */
    );
}