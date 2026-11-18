@extends('layouts.admin')

@section('title', 'Galeri')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Galeri Sekolah</h1>
            <p class="content__description">Kelola foto kegiatan dan penghargaan sekolah.</p>
        </div>
        <button type="button" class="btn btn--brand" id="btn-tambah-foto">
            <x-icon name="plus" />
            Tambah Foto
        </button>
    </div>

    <div id="galeri-grid" class="gallery-grid">
        <p class="empty-state">Memuat galeri...</p>
    </div>

    <div class="modal" id="galeri-modal" role="dialog" aria-modal="true" aria-labelledby="galeri-modal-title">
        <div class="modal__dialog">
            <div class="modal__header">
                <h2 class="modal__title" id="galeri-modal-title">Tambah Foto Galeri</h2>
                <button type="button" class="btn btn--sm btn--icon btn--ghost" data-close-modal aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>

            <form id="form-galeri" class="modal__body stack gap-4">
                <div>
                    <img id="galeri-preview" alt="Preview gambar galeri" class="hidden" style="width: 100%; height: 16rem; object-fit: contain; border-radius: var(--radius-md); background: var(--muted)" />
                    <div id="galeri-placeholder" class="card" style="display: flex; height: 16rem; align-items: center; justify-content: center; border-style: dashed">
                        <x-icon name="image" style="width: 2.5rem; height: 2.5rem; color: var(--muted-foreground)" />
                    </div>
                </div>

                <div class="form-field">
                    <label class="label" for="galeri-gambar" id="galeri-gambar-label">Pilih Gambar</label>
                    <input id="galeri-gambar" type="file" accept="image/jpeg,image/png,image/webp" class="file-input" />
                    <p class="form-hint" id="galeri-gambar-hint">JPG, PNG, WEBP. Maksimal 2 MB.</p>
                    <p class="form-hint" id="galeri-file-baru"></p>
                    <p class="form-error" id="error-gambar"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="galeri-deskripsi">Deskripsi Singkat</label>
                    <textarea id="galeri-deskripsi" class="textarea" rows="3" maxlength="255" placeholder="Contoh: Kegiatan upacara Hari Pendidikan Nasional"></textarea>
                    <p class="form-hint" id="galeri-hitung">0/255 karakter</p>
                    <p class="form-error" id="error-deskripsi"></p>
                </div>

                <p class="form-error" id="galeri-pesan-error" role="alert"></p>
            </form>

            <div class="modal__footer">
                <button type="button" class="btn btn--outline" data-close-modal>Batal</button>
                <button type="submit" form="form-galeri" class="btn btn--brand" id="galeri-simpan">Simpan</button>
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

            const grid = document.getElementById('galeri-grid');
            const modal = document.getElementById('galeri-modal');
            const form = document.getElementById('form-galeri');
            const titleEl = document.getElementById('galeri-modal-title');
            const labelEl = document.getElementById('galeri-gambar-label');
            const hintEl = document.getElementById('galeri-gambar-hint');
            const fileInput = document.getElementById('galeri-gambar');
            const previewImg = document.getElementById('galeri-preview');
            const placeholder = document.getElementById('galeri-placeholder');
            const fileBaru = document.getElementById('galeri-file-baru');
            const deskripsiInput = document.getElementById('galeri-deskripsi');
            const hitungEl = document.getElementById('galeri-hitung');
            const pesanError = document.getElementById('galeri-pesan-error');
            const simpanButton = document.getElementById('galeri-simpan');

            let rows = [];
            let editing = null;
            let selectedFile = null;
            let previewUrl = null;
            let deletingId = null;
            let saving = false;

            const setError = (name, message) => {
                const element = document.getElementById('error-' + name);
                if (element) element.textContent = message || '';
            };

            const clearErrors = () => {
                setError('gambar', '');
                setError('deskripsi', '');
                pesanError.textContent = '';
            };

            const setPreview = (url) => {
                if (previewUrl && previewUrl.startsWith('blob:')) URL.revokeObjectURL(previewUrl);
                previewUrl = url && url.startsWith('blob:') ? url : null;
                if (url) {
                    previewImg.src = url;
                    previewImg.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                } else {
                    previewImg.removeAttribute('src');
                    previewImg.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                }
            };

            const closeModal = () => {
                modal.classList.remove('is-open');
                selectedFile = null;
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
                grid.innerHTML = rows.map((image) => {
                    const id = escapeHtml(image.id);
                    const deskripsi = escapeHtml(image.deskripsi || 'Tanpa deskripsi');
                    const alt = escapeHtml(image.deskripsi || 'Foto kegiatan sekolah');
                    const gambar = image.gambar_url
                        ? '<img src="' + escapeHtml(image.gambar_url) + '" alt="' + alt + '" loading="lazy" style="width: 100%; height: 16rem; object-fit: cover" />'
                        : '<div class="gallery-placeholder" style="height: 16rem"><span class="muted">-</span></div>';
                    const pembuat = escapeHtml((image.pembuat && image.pembuat.name) || 'Tidak tercatat');
                    const hapusLabel = deletingId === image.id ? 'Menghapus...' : 'Hapus';

                    return '<article class="gallery-card">' +
                        gambar +
                        '<div style="padding: 1rem">' +
                            '<p class="text-sm" style="white-space: pre-wrap; overflow-wrap: anywhere">' + deskripsi + '</p>' +
                            '<p class="form-hint mt-2">Diunggah oleh: ' + pembuat + '</p>' +
                            '<div class="row gap-2 mt-4" style="justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 0.75rem">' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="edit" data-id="' + id + '">Edit</button>' +
                                '<button type="button" class="btn btn--sm btn--danger" data-action="hapus" data-id="' + id + '"' + (deletingId !== null ? ' disabled' : '') + '>' + hapusLabel + '</button>' +
                            '</div>' +
                        '</div>' +
                    '</article>';
                }).join('');
            };

            const load = async () => {
                grid.innerHTML = '<p class="empty-state">Memuat galeri...</p>';
                try {
                    const response = await api('/api/galeri');
                    const payload = response?.data?.data ?? response?.data ?? response;
                    rows = Array.isArray(payload) ? payload : [];
                } catch (error) {
                    grid.innerHTML = '<div class="empty-state">' +
                        '<p role="alert">Gagal memuat galeri. Silakan coba lagi.</p>' +
                        '<button type="button" class="btn btn--sm btn--link mt-2" data-action="retry">Coba lagi</button>' +
                    '</div>';
                    return;
                }

                if (!rows.length) {
                    grid.innerHTML = '<div class="empty-state" style="grid-column: 1 / -1; border: 1px dashed var(--border); border-radius: var(--radius-lg)">Belum ada foto. Klik Tambah Foto untuk mengisi galeri.</div>';
                    return;
                }
                render();
            };

            const findRow = (id) => rows.find((row) => String(row.id) === String(id));

            const bukaTambah = () => {
                editing = null;
                form.reset();
                selectedFile = null;
                setPreview(null);
                fileBaru.textContent = '';
                fileInput.value = '';
                hitungEl.textContent = '0/255 karakter';
                titleEl.textContent = 'Tambah Foto Galeri';
                labelEl.textContent = 'Pilih Gambar';
                hintEl.textContent = 'JPG, PNG, WEBP. Maksimal 2 MB.';
                clearErrors();
                modal.classList.add('is-open');
            };

            const bukaEdit = (row) => {
                editing = row;
                form.reset();
                deskripsiInput.value = row.deskripsi ?? '';
                selectedFile = null;
                setPreview(row.gambar_url || null);
                fileBaru.textContent = '';
                fileInput.value = '';
                hitungEl.textContent = deskripsiInput.value.length + '/255 karakter';
                titleEl.textContent = 'Edit Galeri';
                labelEl.textContent = 'Ganti Gambar';
                hintEl.textContent = 'JPG, PNG, WEBP. Maksimal 2 MB. Tidak memilih gambar baru akan mempertahankan gambar lama.';
                clearErrors();
                modal.classList.add('is-open');
            };

            const pilihGambar = (file) => {
                if (!file) return;
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    setError('gambar', 'Format gambar harus JPG, PNG, atau WEBP.');
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    setError('gambar', 'Ukuran gambar maksimal 2 MB.');
                    return;
                }
                selectedFile = file;
                setError('gambar', '');
                setPreview(URL.createObjectURL(file));
                fileBaru.textContent = 'Gambar baru: ' + file.name;
            };

            const hapus = async (id) => {
                if (deletingId !== null) return;
                if (!confirm('Hapus foto ini secara permanen?')) return;

                deletingId = id;
                render();
                try {
                    await api('/api/galeri/' + id, { method: 'DELETE' });
                    deletingId = null;
                    await load();
                } catch (error) {
                    deletingId = null;
                    alert(error.data?.message || 'Gagal menghapus foto.');
                    render();
                }
            };

            grid.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                if (button.dataset.action === 'retry') {
                    load();
                    return;
                }
                const row = findRow(button.dataset.id);
                if (!row) return;
                if (button.dataset.action === 'edit') bukaEdit(row);
                if (button.dataset.action === 'hapus') hapus(row.id);
            });

            const simpan = async (event) => {
                event.preventDefault();
                if (saving) return;

                if (!editing && !selectedFile) {
                    setError('gambar', 'Gambar wajib dipilih.');
                    return;
                }

                saving = true;
                clearErrors();
                simpanButton.textContent = 'Menyimpan...';
                simpanButton.disabled = true;

                const payload = new FormData();
                payload.append('deskripsi', deskripsiInput.value);
                if (selectedFile) payload.append('gambar', selectedFile);
                if (editing) payload.append('_method', 'PUT');

                try {
                    await api(editing ? '/api/galeri/' + editing.id : '/api/galeri', {
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
                    } else {
                        pesanError.textContent = error.status === 413
                            ? 'Ukuran file terlalu besar untuk server.'
                            : (error.data?.message || 'Gagal menyimpan galeri.');
                    }
                } finally {
                    saving = false;
                    simpanButton.textContent = 'Simpan';
                    simpanButton.disabled = false;
                }
            };

            document.getElementById('btn-tambah-foto').addEventListener('click', bukaTambah);
            form.addEventListener('submit', simpan);
            fileInput.addEventListener('change', (event) => {
                pilihGambar(event.target.files && event.target.files[0]);
                event.target.value = '';
            });
            deskripsiInput.addEventListener('input', () => {
                hitungEl.textContent = deskripsiInput.value.length + '/255 karakter';
            });

            load();
        })();
    </script>
@endpush
