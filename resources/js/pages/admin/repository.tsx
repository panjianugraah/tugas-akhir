import axios from 'axios';
import { Download, Pencil, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import DataTablePage from './data-table-page';
import RepositoryForm from './repository-form';
import { deleteRepository, getAllRepository } from '../../../api/repository_api';
import RepositoryKodeForm from './repository-kode-form';

export default function AdminRepository() {
    const [rows, setRows] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<any | null>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError('');

        try {
            setRows(await getAllRepository());
        } catch {
            setError('Gagal memuat dokumen.');
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

    const bukaEdit = (row: any) => {
        setEditing(row);
        setFormOpen(true);
    };

    const hapus = async (id: number) => {
        if (!confirm('Hapus dokumen ini secara permanen?')) return;

        try {
            await deleteRepository(id);
            await load();
        } catch (err) {
            alert(
                axios.isAxiosError(err)
                    ? err.response?.data?.message ??
                    'Gagal menghapus dokumen.'
                    : 'Gagal menghapus dokumen.'
            );
        }
    };

    type PermissionProps = {
        can: {
            access_admin: boolean;
            manage_users: boolean;
        };
    };

    const { can } = usePage<PermissionProps>().props;

    return (
        <>
            {can.access_admin && <RepositoryKodeForm />}

            {error && (
                <p role="alert" className="px-4 pt-4 text-sm text-red-600">
                    {error}
                </p>
            )}

            <DataTablePage
                title="Repository"
                description="Kelola dokumen dan berkas sekolah"
                addLabel="Tambah Dokumen"
                onAdd={bukaTambah}
                rows={rows}
                loading={loading}
                columns={[
                    { key: 'judul', label: 'Judul Materi' },
                    {
                        key: 'mata_pelajaran.nama',
                        label: 'Mata Pelajaran',
                        render: (row) => row.mata_pelajaran?.nama ?? 'Belum ditentukan',
                    },
                    {
                        key: 'nama_file',
                        label: 'File',
                        render: (row) => row.nama_file ?? '-',
                    },
                    {
                        key: 'pembuat',
                        label: 'Dibuat Oleh',
                        render: (row) => row.pembuat?.name ?? 'Tidak tercatat',
                    },
                ]}
                actions={(row) => (
                    <>
                        {/* Anchor biasa untuk unduhan, bukan Link Inertia. */}
                        <a
                            href={`/api/repository/${row.id}/download`}
                            title="Unduh"
                            className="rounded p-1.5 text-green-700 hover:bg-green-50"
                        >
                            <Download className="size-4" />
                        </a>

                        <button
                            onClick={() => bukaEdit(row)}
                            title="Edit"
                            className="rounded p-1.5 text-blue-700 hover:bg-blue-50"
                        >
                            <Pencil className="size-4" />
                        </button>

                        <button
                            onClick={() => hapus(row.id)}
                            title="Hapus"
                            className="rounded p-1.5 text-red-700 hover:bg-red-50"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </>
                )}
            />

            <RepositoryForm
                open={formOpen}
                data={editing}
                onClose={() => setFormOpen(false)}
                onSaved={load}
            />
        </>
    );
}