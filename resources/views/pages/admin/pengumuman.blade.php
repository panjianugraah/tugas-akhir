@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Pengumuman</h1>
            <p class="content__description">Kelola pengumuman untuk siswa, orang tua, dan umum</p>
        </div>
        <button type="button" class="btn btn--brand" id="btn-tambah-pengumuman">
            <x-icon name="plus" />
            Buat Pengumuman
        </button>
    </div>

    <div class="filter-bar">
        <input type="search" id="pengumuman-search" class="input search-input" placeholder="Cari..." aria-label="Cari pengumuman" />
        <select id="filter-target" class="select" aria-label="Filter target">
            <option value="">Semua Target</option>
            <option value="Siswa">Siswa</option>
            <option value="Guru">Guru</option>
            <option value="Orang Tua">Orang Tua</option>
            <option value="Umum">Umum</option>
        </select>
        <select id="filter-status" class="select" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="terbit">Terbit</option>
            <option value="draft">Draft</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Berlaku Sampai</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="pengumuman-rows">
                <tr><td colspan="7" class="empty-state">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
    <p id="pengumuman-count" class="form-hint mt-2">Menampilkan 0 dari 0 data</p>

    <div class="modal" id="pengumuman-modal" role="dialog" aria-modal="true" aria-labelledby="pengumuman-modal-title">
        <div class="modal__dialog modal__dialog--wide">
            <div class="modal__header">
                <h2 class="modal__title" id="pengumuman-modal-title">Buat Pengumuman</h2>
                <button type="button" class="btn btn--sm btn--icon btn--ghost" data-close-modal aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>

            <form id="form-pengumuman" class="modal__body stack gap-4">
                <input type="hidden" name="status" id="pengumuman-status" value="terbit" />

                <div class="form-field">
                    <span class="label">Gambar Sampul</span>
                    <div id="pengumuman-dropzone" class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 2rem 1rem; border-style: dashed; cursor: pointer; text-align: center;">
                        <x-icon name="image" style="width: 2rem; height: 2rem; color: var(--muted-foreground)" />
                        <p class="text-sm"><span class="font-semibold" style="color: var(--brand-700)">Klik untuk upload</span> atau seret gambar ke sini</p>
                        <p class="form-hint">JPG, PNG, WEBP · maks. 2 MB</p>
                    </div>
                    <div id="pengumuman-preview-wrap" class="hidden">
                        <img id="pengumuman-preview" alt="Preview" style="width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border: 1px solid var(--border); border-radius: var(--radius-md)" />
                        <div class="row gap-2 mt-2" style="justify-content: flex-end">
                            <button type="button" class="btn btn--sm btn--outline" id="pengumuman-ganti">Ganti</button>
                            <button type="button" class="btn btn--sm btn--icon btn--outline" id="pengumuman-buang" title="Hapus gambar" aria-label="Hapus gambar">
                                <x-icon name="trash" />
                            </button>
                        </div>
                        <p id="pengumuman-file-info" class="form-hint"></p>
                    </div>
                    <input id="pengumuman-gambar" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" />
                    <p class="form-error" id="error-gambar"></p>
                </div>

                <div class="form-field">
                    <label class="label label--required" for="pengumuman-judul">Judul</label>
                    <input id="pengumuman-judul" class="input" type="text" placeholder="contoh: Jadwal Penilaian Tengah Semester Ganjil" />
                    <p class="form-error" id="error-judul"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="pengumuman-berlaku">Berlaku Sampai</label>
                    <input id="pengumuman-berlaku" class="input" type="date" />
                    <p class="form-hint">Kosongkan jika berlaku tanpa batas.</p>
                    <p class="form-error" id="error-berlaku_sampai"></p>
                </div>

                <div class="form-field">
                    <label class="label label--required" for="pengumuman-isi">Isi Pengumuman</label>
                    <textarea id="pengumuman-isi" class="textarea" rows="8" placeholder="Tulis isi pengumuman di sini..."></textarea>
                    <p class="form-error" id="error-isi"></p>
                </div>
            </form>

            <div class="modal__footer">
                <button type="button" class="btn btn--outline" data-close-modal>Batal</button>
                <button type="button" class="btn btn--outline" id="pengumuman-simpan-draft" style="color: var(--brand-700); border-color: var(--brand-700)">Simpan Draft</button>
                <button type="submit" form="form-pengumuman" class="btn btn--brand" id="pengumuman-simpan-terbit">Terbitkan</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            async function api(url, options = {}) {
                const res = await fetch(url, {
                    ...options,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        ...(options.headers || {}),
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw Object.assign(new Error(data.message || 'Gagal.'), { status: res.status, data });
                return data;
            }

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[char]);

            const rowsEl = document.getElementById('pengumuman-rows');
            const countEl = document.getElementById('pengumuman-count');
            const searchEl = document.getElementById('pengumuman-search');
            const targetEl = document.getElementById('filter-target');
            const statusEl = document.getElementById('filter-status');
            const modal = document.getElementById('pengumuman-modal');
            const form = document.getElementById('form-pengumuman');
            const titleEl = document.getElementById('pengumuman-modal-title');
            const statusInput = document.getElementById('pengumuman-status');
            const judulInput = document.getElementById('pengumuman-judul');
            const isiInput = document.getElementById('pengumuman-isi');
            const berlakuInput = document.getElementById('pengumuman-berlaku');
            const fileInput = document.getElementById('pengumuman-gambar');
            const dropzone = document.getElementById('pengumuman-dropzone');
            const previewWrap = document.getElementById('pengumuman-preview-wrap');
            const previewImg = document.getElementById('pengumuman-preview');
            const fileInfo = document.getElementById('pengumuman-file-info');
            const draftButton = document.getElementById('pengumuman-simpan-draft');
            const terbitButton = document.getElementById('pengumuman-simpan-terbit');
            const errorNames = ['gambar', 'judul', 'berlaku_sampai', 'isi'];

            let rows = [];
            let editing = null;
            let selectedFile = null;
            let previewUrl = null;
            let hapusGambar = false;
            let saving = false;

            const setError = (name, message) => {
                const element = document.getElementById('error-' + name);
                if (element) element.textContent = message || '';
            };

            const clearErrors = () => errorNames.forEach((name) => setError(name, ''));

            const setPreview = (url, fileName, fileSize) => {
                if (previewUrl && previewUrl.startsWith('blob:')) URL.revokeObjectURL(previewUrl);
                previewUrl = url && url.startsWith('blob:') ? url : null;
                if (url) {
                    previewImg.src = url;
                    previewWrap.classList.remove('hidden');
                    dropzone.classList.add('hidden');
                    fileInfo.textContent = fileName
                        ? fileName + ' · ' + (fileSize / 1024).toFixed(0) + ' KB'
                        : '';
                } else {
                    previewImg.removeAttribute('src');
                    previewWrap.classList.add('hidden');
                    dropzone.classList.remove('hidden');
                    fileInfo.textContent = '';
                }
            };

            const closeModal = () => {
                modal.classList.remove('is-open');
                selectedFile = null;
                hapusGambar = false;
                setPreview(null);
                clearErrors();
            };

            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            modal.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', closeModal);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
            });

            const render = () => {
                const query = searchEl.value.trim().toLowerCase();
                const filtered = rows.filter((row) => [
                    row.judul,
                    row.berlaku_sampai,
                    row.status,
                    row.pembuat && row.pembuat.name,
                ].some((value) => String(value ?? '').toLowerCase().includes(query)));

                if (!filtered.length) {
                    rowsEl.innerHTML = '<tr><td colspan="7" class="empty-state">Tidak ada data.</td></tr>';
                } else {
                    rowsEl.innerHTML = filtered.map((row, index) => {
                        const id = escapeHtml(row.id);
                        const judul = escapeHtml(row.judul);
                        const gambar = row.gambar_url
                            ? '<img src="' + escapeHtml(row.gambar_url) + '" alt="' + judul + '" class="table__thumb" />'
                            : '<div class="table__thumb" style="display: flex; align-items: center; justify-content: center; background: var(--muted)"><span class="text-xs muted">-</span></div>';
                        const status = escapeHtml(row.status);
                        const statusClass = status === 'terbit' ? 'badge--success' : 'badge--neutral';
                        const statusAction = row.status === 'draft' ? 'terbit' : 'draft';
                        const statusLabel = row.status === 'draft' ? 'Terbitkan' : 'Jadikan Draft';

                        return '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + gambar + '</td>' +
                            '<td>' + judul + '</td>' +
                            '<td>' + escapeHtml(row.berlaku_sampai || 'Tanpa batas') + '</td>' +
                            '<td><span class="badge ' + statusClass + '" style="text-transform: capitalize">' + status + '</span></td>' +
                            '<td>' + escapeHtml((row.pembuat && row.pembuat.name) || 'Tidak tercatat') + '</td>' +
                            '<td><div class="table__actions">' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="edit" data-id="' + id + '" title="Edit">Edit</button>' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="status" data-id="' + id + '" data-status="' + statusAction + '" title="' + statusLabel + '">' + statusLabel + '</button>' +
                                '<button type="button" class="btn btn--sm btn--danger" data-action="hapus" data-id="' + id + '" title="Hapus">Hapus</button>' +
                            '</div></td>' +
                        '</tr>';
                    }).join('');
                }

                countEl.textContent = 'Menampilkan ' + filtered.length + ' dari ' + rows.length + ' data';
            };

            const load = async () => {
                const params = new URLSearchParams();
                if (statusEl.value) params.set('status', statusEl.value);
                if (targetEl.value) params.set('target', targetEl.value);
                const query = params.toString();

                rowsEl.innerHTML = '<tr><td colspan="7" class="empty-state">Memuat data...</td></tr>';
                try {
                    const response = await api(query ? '/api/pengumuman?' + query : '/api/pengumuman');
                    const payload = response?.data?.data ?? response?.data ?? response;
                    rows = Array.isArray(payload) ? payload : [];
                } catch (error) {
                    rows = [];
                }
                render();
            };

            const findRow = (id) => rows.find((row) => String(row.id) === String(id));

            const ubahStatus = async (id, status) => {
                try {
                    await api('/api/pengumuman/' + id, {
                        method: 'PUT',
                        body: JSON.stringify({ status: status }),
                        headers: { 'Content-Type': 'application/json' },
                    });
                    await load();
                } catch (error) {
                    alert(error.data?.message || 'Gagal mengubah status.');
                }
            };

            const hapus = async (id) => {
                if (!confirm('Hapus pengumuman ini?')) return;
                try {
                    await api('/api/pengumuman/' + id, { method: 'DELETE' });
                    await load();
                } catch (error) {
                    alert(error.data?.message || 'Gagal menghapus pengumuman.');
                }
            };

            rowsEl.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const row = findRow(button.dataset.id);
                if (!row) return;
                if (button.dataset.action === 'edit') bukaEdit(row);
                if (button.dataset.action === 'status') ubahStatus(row.id, button.dataset.status);
                if (button.dataset.action === 'hapus') hapus(row.id);
            });

            const setButtons = (disabled) => {
                modal.querySelectorAll('button').forEach((button) => { button.disabled = disabled; });
            };

            const pilihGambar = (file) => {
                if (!file) return;
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    setError('gambar', 'Gambar harus berformat jpg, png, atau webp.');
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    setError('gambar', 'Ukuran gambar maksimal 2 MB.');
                    return;
                }
                selectedFile = file;
                hapusGambar = false;
                setError('gambar', '');
                setPreview(URL.createObjectURL(file), file.name, file.size);
            };

            const buangGambar = () => {
                selectedFile = null;
                fileInput.value = '';
                if (editing && editing.gambar_url) hapusGambar = true;
                setPreview(null);
            };

            const bukaTambah = () => {
                editing = null;
                form.reset();
                statusInput.value = 'terbit';
                selectedFile = null;
                hapusGambar = false;
                setPreview(null);
                clearErrors();
                titleEl.textContent = 'Buat Pengumuman';
                modal.classList.add('is-open');
            };

            const bukaEdit = (row) => {
                editing = row;
                form.reset();
                judulInput.value = row.judul ?? '';
                isiInput.value = row.isi ?? '';
                berlakuInput.value = String(row.berlaku_sampai ?? '').slice(0, 10);
                statusInput.value = row.status ?? 'draft';
                selectedFile = null;
                hapusGambar = false;
                setPreview(row.gambar_url || null);
                clearErrors();
                titleEl.textContent = 'Edit Pengumuman';
                modal.classList.add('is-open');
            };

            const simpan = async (status) => {
                if (saving) return;
                saving = true;
                setButtons(true);
                clearErrors();
                statusInput.value = status;

                const payload = new FormData();
                payload.append('judul', judulInput.value);
                payload.append('isi', isiInput.value);
                payload.append('berlaku_sampai', berlakuInput.value);
                payload.append('status', status);
                if (selectedFile) payload.append('gambar', selectedFile);
                if (hapusGambar) payload.append('hapus_gambar', '1');
                if (editing) payload.append('_method', 'PUT');

                try {
                    await api(editing ? '/api/pengumuman/' + editing.id : '/api/pengumuman', {
                        method: 'POST',
                        body: payload,
                    });
                    closeModal();
                    await load();
                } catch (error) {
                    if (error.status === 422 && error.data?.errors) {
                        Object.entries(error.data.errors).forEach(([name, messages]) => {
                            setError(name, Array.isArray(messages) ? messages[0] : String(messages));
                        });
                    } else if (error.status === 413) {
                        setError('gambar', 'File terlalu besar untuk server.');
                    } else {
                        alert(error.data?.message || 'Gagal menyimpan pengumuman.');
                    }
                } finally {
                    saving = false;
                    setButtons(false);
                }
            };

            document.getElementById('btn-tambah-pengumuman').addEventListener('click', bukaTambah);
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                simpan('terbit');
            });
            draftButton.addEventListener('click', () => simpan('draft'));
            fileInput.addEventListener('change', (event) => {
                pilihGambar(event.target.files && event.target.files[0]);
                event.target.value = '';
            });
            dropzone.addEventListener('click', () => fileInput.click());
            document.getElementById('pengumuman-ganti').addEventListener('click', () => fileInput.click());
            document.getElementById('pengumuman-buang').addEventListener('click', buangGambar);
            dropzone.addEventListener('dragover', (event) => { event.preventDefault(); });
            dropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                pilihGambar(event.dataTransfer.files && event.dataTransfer.files[0]);
            });
            searchEl.addEventListener('input', render);
            targetEl.addEventListener('change', load);
            statusEl.addEventListener('change', load);

            load();
        })();
    </script>
@endpush
