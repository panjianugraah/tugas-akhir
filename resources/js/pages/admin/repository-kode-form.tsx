import axios from 'axios';
import { useState, type FormEvent } from 'react';
import { resetAksesRepository, simpanKodeRepository } from '../../../api/repository_api';

export default function RepositoryKodeForm() {
    const [kode, setKode] = useState('');
    const [saving, setSaving] = useState(false);
    const [resetting, setResetting] = useState(false);
    const [pesan, setPesan] = useState('');

    const simpan = async (event: FormEvent) => {
        event.preventDefault();

        if (!confirm('Simpan kode baru? Akses pengunjung lama akan dibatalkan.')) {
            return;
        }

        setSaving(true);
        setPesan('');

        try {
            const response = await simpanKodeRepository(kode);
            setKode('');
            setPesan(response.message);
        } catch (err) {
            setPesan(
                axios.isAxiosError(err)
                    ? err.response?.data?.errors?.kode?.[0] ??
                      err.response?.data?.message ??
                      'Gagal menyimpan kode.'
                    : 'Gagal menyimpan kode.'
            );
        } finally {
            setSaving(false);
        }
    };

    const resetAkses = async () => {
        if (!confirm('Reset akses seluruh pengunjung? Mereka harus memasukkan kode kembali.')) {
            return;
        }

        setResetting(true);
        setPesan('');

        try {
            const response = await resetAksesRepository();
            setPesan(response.message);
        } catch (err) {
            setPesan(
                axios.isAxiosError(err)
                    ? err.response?.data?.message ?? 'Gagal mereset akses pengunjung.'
                    : 'Gagal mereset akses pengunjung.'
            );
        } finally {
            setResetting(false);
        }
    };

    return (
        <form onSubmit={simpan} className="m-4 rounded-xl border p-5">
            <h2 className="font-semibold">Kode Akses Repository</h2>

            <p className="mt-2 text-sm text-muted-foreground">
                Minimal 8 karakter. Sampaikan kode secara langsung kepada
                warga sekolah.
            </p>

            <input
                type="password"
                aria-label="Kode akses baru"
                autoComplete="new-password"
                value={kode}
                onChange={(event) => setKode(event.target.value)}
                required
                minLength={8}
                maxLength={100}
                className="mt-3 w-full max-w-sm rounded-lg border px-3 py-2"
            />

            <button
                disabled={saving || resetting}
                className="mt-3 block rounded-lg bg-blue-700 px-4 py-2 text-sm text-white disabled:opacity-50"
            >
                {saving ? 'Menyimpan...' : 'Simpan Kode'}
            </button>

            <div className="mt-5 border-t pt-4">
                <p className="text-sm text-muted-foreground">
                    Reset meminta seluruh pengunjung memasukkan kode kembali.
                    Kode akses tidak berubah.
                </p>
                <button
                    type="button"
                    onClick={resetAkses}
                    disabled={saving || resetting}
                    className="mt-3 rounded-lg border border-red-700 px-4 py-2 text-sm text-red-700 hover:bg-red-50 disabled:opacity-50"
                >
                    {resetting ? 'Mereset...' : 'Reset Akses Pengunjung'}
                </button>
            </div>

            {pesan && (
                <p role="status" className="mt-3 text-sm">
                    {pesan}
                </p>
            )}
        </form>
    );
}
