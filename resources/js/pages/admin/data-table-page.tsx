import { Head } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useState, type ReactNode } from 'react';

export type Column = {
    key: string;                      // boleh dot-path: 'kelas.nama_kelas'
    label: string;
    render?: (row: any) => ReactNode;
    searchable?: boolean;             // default true
};

type Props = {
    title: string;
    description?: string;
    columns: Column[];
    rows: any[];
    addLabel?: string;
    loading?: boolean;
    toolbar?: ReactNode;
    actions?: (row: any) => ReactNode;
    onAdd?: () => void;
    onEdit?: (row: any) => void;
    onDelete?: (row: any) => void;
};

// Ambil nilai bertingkat: get(row, 'kelas.nama_kelas')
export const get = (obj: any, path: string) =>
    path.split('.').reduce((o, k) => o?.[k], obj);

export function StatusBadge({ value }: { value: string }) {
    const colors: Record<string, string> = {
        aktif: 'bg-green-100 text-green-700',
        diterima: 'bg-green-100 text-green-700',
        terbit: 'bg-green-100 text-green-700',
        lulus: 'bg-blue-100 text-blue-700',
        menunggu: 'bg-yellow-100 text-yellow-700',
        pindah: 'bg-yellow-100 text-yellow-700',
        draft: 'bg-gray-100 text-gray-700',
        ditolak: 'bg-red-100 text-red-700',
        nonaktif: 'bg-red-100 text-red-700',
        keluar: 'bg-red-100 text-red-700',
    };
    return (
        <span className={`rounded-full px-2 py-0.5 text-xs font-medium capitalize ${colors[value] ?? 'bg-gray-100 text-gray-700'}`}>
            {value}
        </span>
    );
}

export default function DataTablePage({
    title, description, columns, rows, addLabel, loading, toolbar, actions, onAdd, onEdit, onDelete,
}: Props) {
    const [search, setSearch] = useState('');
    const q = search.toLowerCase();

    const filtered = rows.filter((row) =>
        columns
            .filter((col) => col.searchable !== false)
            .some((col) => String(get(row, col.key) ?? '').toLowerCase().includes(q)),
    );

    return (
        <>
            <Head title={title} />

            <div className="flex flex-col gap-4 p-4 md:p-6">
                {/* Header */}
                <div className="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold">{title}</h1>
                        {description && <p className="text-sm text-muted-foreground">{description}</p>}
                    </div>
                    {addLabel && (
                        <button
                            onClick={onAdd ?? (() => alert(`Form "${addLabel}" belum dibuat`))}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800"
                        >
                            <Plus className="size-4" /> {addLabel}
                        </button>
                    )}
                </div>

                {/* Toolbar */}
                <div className="flex flex-col gap-3 md:flex-row md:items-center">
                    <div className="relative max-w-sm flex-1">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari..."
                            className="w-full rounded-lg border bg-background py-2 pr-3 pl-9 text-sm focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />
                    </div>
                    {toolbar}
                </div>

                {/* Tabel */}
                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left">
                            <tr>
                                <th className="w-12 p-3">No</th>
                                {columns.map((col) => (
                                    <th key={col.key} className="p-3 font-semibold">{col.label}</th>
                                ))}
                                <th className="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan={columns.length + 2} className="p-6 text-center text-muted-foreground">Memuat data...</td></tr>
                            ) : filtered.length === 0 ? (
                                <tr><td colSpan={columns.length + 2} className="p-6 text-center text-muted-foreground">Tidak ada data.</td></tr>
                            ) : (
                                filtered.map((row, i) => (
                                    <tr key={row.id ?? i} className="border-t hover:bg-muted/30">
                                        <td className="p-3">{i + 1}</td>
                                        {columns.map((col) => (
                                            <td key={col.key} className="p-3">
                                                {col.render ? col.render(row) : (get(row, col.key) ?? '-')}
                                            </td>
                                        ))}
                                        <td className="p-3">
                                            <div className="flex justify-end gap-1">
                                                {actions ? actions(row) : (
                                                    <>
                                                        <button onClick={() => onEdit?.(row)} className="rounded p-1.5 text-blue-700 hover:bg-blue-50" title="Edit">
                                                            <Pencil className="size-4" />
                                                        </button>
                                                        <button onClick={() => onDelete?.(row)} className="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus">
                                                            <Trash2 className="size-4" />
                                                        </button>
                                                    </>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <p className="text-xs text-muted-foreground">Menampilkan {filtered.length} dari {rows.length} data</p>
            </div>
        </>
    );
}