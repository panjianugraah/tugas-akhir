import axios from 'axios';
import { Head } from '@inertiajs/react';
import { ImageOff, Pencil, Plus, Trash2 } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { deleteGaleri, getAllGaleri } from '../../../api/galeri_api';
import GaleriForm, { type GaleriItem } from '../admin/galeri-form';

export default function AdminGaleri() {
  const [rows, setRows] = useState<GaleriItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<GaleriItem | null>(null);
  const [deletingId, setDeletingId] = useState<number | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError('');

    try {
      setRows(await getAllGaleri());
    } catch {
      setError('Gagal memuat galeri. Silakan coba lagi.');
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

  const bukaEdit = (row: GaleriItem) => {
    setEditing(row);
    setFormOpen(true);
  };

  const hapus = async (id: number) => {
    if (deletingId !== null) return;
    if (!confirm('Hapus foto ini secara permanen?')) return;

    setDeletingId(id);

    try {
      await deleteGaleri(id);
      await load();
    } catch (err) {
      alert(
        axios.isAxiosError(err)
          ? err.response?.data?.message ?? 'Gagal menghapus foto.'
          : 'Gagal menghapus foto.'
      );
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <>
      <Head title="Galeri" />

      <section className="p-4 md:p-6">
        <div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <h1 className="text-2xl font-bold">
              Galeri Sekolah
            </h1>
            <p className="mt-1 text-sm text-muted-foreground">
              Kelola foto kegiatan dan penghargaan sekolah.
            </p>
          </div>

          <button
            onClick={bukaTambah}
            className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800"
          >
            <Plus className="size-4" />
            Tambah Foto
          </button>
        </div>

        {loading ? (
          <p className="py-10 text-center text-muted-foreground">
            Memuat galeri...
          </p>
        ) : error ? (
          <div className="py-10 text-center">
            <p role="alert" className="text-red-600">
              {error}
            </p>
            <button
              onClick={() => void load()}
              className="mt-3 text-sm text-blue-700 underline"
            >
              Coba lagi
            </button>
          </div>
        ) : rows.length === 0 ? (
          <div className="rounded-xl border border-dashed p-10 text-center text-muted-foreground">
            Belum ada foto. Klik Tambah Foto untuk mengisi galeri.
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {rows.map((image) => (
              <article
                key={image.id}
                className="overflow-hidden rounded-xl border bg-background shadow-sm"
              >
                <div className="group overflow-hidden bg-muted">
                  {image.gambar_url ? (
                    <img
                      src={image.gambar_url}
                      alt={
                        image.deskripsi ||
                        'Foto kegiatan sekolah'
                      }
                      loading="lazy"
                      className="h-64 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                  ) : (
                    <div className="flex h-64 items-center justify-center">
                      <ImageOff className="size-10 text-muted-foreground" />
                    </div>
                  )}
                </div>

                <div className="p-4">
                  <p className="whitespace-pre-wrap break-words text-sm">
                    {image.deskripsi || 'Tanpa deskripsi'}
                  </p>

                  <p className="mt-2 text-xs text-muted-foreground">
                    Diunggah oleh: {image.pembuat?.name ?? 'Tidak tercatat'}
                  </p>

                  <div className="mt-4 flex justify-end gap-2 border-t pt-3">
                    <button
                      onClick={() => bukaEdit(image)}
                      className="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm text-blue-700 hover:bg-blue-50"
                    >
                      <Pencil className="size-4" />
                      Edit
                    </button>

                    <button
                      onClick={() => hapus(image.id)}
                      disabled={deletingId !== null}
                      className="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm text-red-700 hover:bg-red-50 disabled:opacity-50"
                    >
                      <Trash2 className="size-4" />
                      {deletingId === image.id
                        ? 'Menghapus...'
                        : 'Hapus'}
                    </button>
                  </div>
                </div>
              </article>
            ))}
          </div>
        )}
      </section>

      <GaleriForm
        open={formOpen}
        data={editing}
        onClose={() => setFormOpen(false)}
        onSaved={load}
      />
    </>
  );
}