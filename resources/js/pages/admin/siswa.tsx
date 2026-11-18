import { GraduationCap, RotateCcw, Trash2, Pencil } from 'lucide-react';
import { useEffect, useState } from 'react';
import DataTablePage, { StatusBadge } from '../admin/data-table-page';
import { deleteSiswa, getAllSiswa, updateStatusSiswa } from '../../../api/siswa_api';
import SiswaForm, { type SiswaItem } from './siswa-form';
import { getAllKelas } from '../../../api/kelas_api';

const selectClass = 'rounded-lg border bg-background px-3 py-2 text-sm';

export default function AdminSiswa() {
    const [rows, setRows] = useState<any[]>([]);
    const [kelas, setKelas] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [status, setStatus] = useState('');
    const [kelasId, setKelasId] = useState('');
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<SiswaItem | null>(null);

    const bukaTambah = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const bukaEdit = (row: SiswaItem) => {
        setEditing(row);
        setFormOpen(true);
    };

    const load = async () => {
        setLoading(true);
        try {
            const params: Record<string, any> = {};
            if (status) params.status = status;
            if (kelasId) params.kelas_id = kelasId;
            setRows(await getAllSiswa(params));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { load(); }, [status, kelasId]);

    // Daftar kelas untuk filter (sekali saja)
    useEffect(() => { getAllKelas().then(setKelas); }, []);

    const ubahStatus = async (id: number, s: string) => {
        try {
            await updateStatusSiswa(id, s);
            load();
        } catch (error: any) {
            alert(error.response?.data?.message ?? 'Gagal mengubah status.');
        }
    };

    const hapus = async (id: number) => {
        if (!confirm('Hapus data siswa ini?')) return;
        await deleteSiswa(id);
        load();
    };

    return (
        <>
            <DataTablePage
                title="Data Siswa"
                description="Kelola data siswa aktif, lulus, dan mutasi"
                rows={rows}
                loading={loading}
                addLabel="Tambah Siswa"
                onAdd={bukaTambah}
                toolbar={
                    <>
                        <select value={kelasId} onChange={(e) => setKelasId(e.target.value)} className={selectClass}>
                            <option value="">Semua Kelas</option>
                            {kelas.map((k) => <option key={k.id} value={k.id}>{k.nama_kelas}</option>)}
                        </select>
                        <select value={status} onChange={(e) => setStatus(e.target.value)} className={selectClass}>
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                        </select>
                    </>
                }
                columns={[
                    { key: 'nis', label: 'NIS' },
                    { key: 'nisn', label: 'NISN' },
                    { key: 'nama_lengkap', label: 'Nama' },
                    { key: 'jenis_kelamin', label: 'L/P' },
                    { key: 'kelas', label: 'Kelas', render: (r) => r.kelas?.nama_kelas ?? '-' },
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
                        {r.status === 'aktif' ? (
                            <button onClick={() => ubahStatus(r.id, 'lulus')} className="rounded p-1.5 text-blue-700 hover:bg-blue-50" title="Luluskan"><GraduationCap className="size-4" /></button>
                        ) : (
                            <button onClick={() => ubahStatus(r.id, 'aktif')} className="rounded p-1.5 text-green-700 hover:bg-green-50" title="Aktifkan Kembali"><RotateCcw className="size-4" /></button>
                        )}
                        <button onClick={() => hapus(r.id)} className="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus"><Trash2 className="size-4" /></button>
                    </>
                )}
            />
            <SiswaForm
                open={formOpen}
                data={editing}
                onClose={() => setFormOpen(false)}
                onSaved={load}
            />
        </>
    );
}