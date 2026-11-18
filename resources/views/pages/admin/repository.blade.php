@extends('layouts.admin')

@section('title', 'Repository')

@section('content')
    @can('access-admin')
        <div class="card" style="margin-bottom: 1.5rem">
            <div class="card__header">
                <h2 class="card__title">Kode Akses Repository</h2>
            </div>
            <div class="card__body">
                <p class="text-sm muted">Minimal 8 karakter. Sampaikan kode secara langsung kepada warga sekolah.</p>

                <form id="form-kode-akses" class="mt-4">
                    <input
                        type="password"
                        id="kode-akses"
                        class="input"
                        aria-label="Kode akses baru"
                        autocomplete="new-password"
                        required
                        minlength="8"
                        maxlength="100"
                    />
                    <button type="submit" class="btn btn--brand mt-4" id="simpan-kode">Simpan Kode</button>
                </form>

                <div class="mt-6" style="border-top: 1px solid var(--border); padding-top: 1rem">
                    <p class="text-sm muted">Reset meminta seluruh pengunjung memasukkan kode kembali. Kode akses tidak berubah.</p>
                    <button type="button" class="btn btn--outline mt-4" id="reset-akses" style="color: var(--red-700); border-color: var(--red-700)">Reset Akses Pengunjung</button>
                </div>

                <p id="kode-pesan" class="text-sm mt-4" role="status"></p>
            </div>
        </div>
    @endcan

    <div class="content__header">
        <div>
            <h1 class="content__title">Repository</h1>
            <p class="content__description">Kelola dokumen dan berkas sekolah</p>
        </div>
        <button type="button" class="btn btn--brand" id="btn-tambah-dokumen">
            <x-icon name="plus" />
            Tambah Dokumen
        </button>
    </div>

    <div id="repository-load-error" class="alert alert--danger hidden" role="alert" style="margin-bottom: 1.5rem">
        <x-icon name="alert" />
        <span>Gagal memuat dokumen.</span>
        <button type="button" class="btn btn--sm btn--link" id="repository-coba-lagi">Coba lagi</button>
    </div>

    <div class="filter-bar">
        <input type="search" id="repository-search" class="input search-input" placeholder="Cari..." aria-label="Cari dokumen" />
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Materi</th>
                    <th>Mata Pelajaran</th>
                    <th>File</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="repository-rows">
                <tr><td colspan="6" class="empty-state">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
    <p id="repository-count" class="form-hint mt-2">Menampilkan 0 dari 0 data</p>

    <div class="modal" id="repository-modal" role="dialog" aria-modal="true" aria-labelledby="repository-modal-title">
        <div class="modal__dialog">
            <div class="modal__header">
                <h2 class="modal__title" id="repository-modal-title">Tambah Materi</h2>
                <button type="button" class="btn btn--sm btn--icon btn--ghost" data-close-modal aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>

            <form id="form-repository" class="modal__body stack gap-4">
                <input type="hidden" name="mata_pelajaran_id" id="repository-mapel-id" />

                <div class="form-field">
                    <label class="label" for="repository-mapel">Mata Pelajaran</label>
                    <select id="repository-mapel" class="select" required>
                        <option value="">Pilih mata pelajaran</option>
                    </select>
                    <p class="form-error" id="error-mata_pelajaran_id"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="repository-judul">Judul</label>
                    <input id="repository-judul" class="input" type="text" required maxlength="255" />
                    <p class="form-error" id="error-judul"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="repository-deskripsi">Deskripsi</label>
                    <textarea id="repository-deskripsi" class="textarea" rows="3" maxlength="5000"></textarea>
                    <p class="form-error" id="error-deskripsi"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="repository-file">File Materi</label>
                    <p id="repository-file-saat-ini" class="form-hint"></p>
                    <input
                        id="repository-file"
                        type="file"
                        class="file-input"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip"
                    />
                    <p class="form-error" id="error-file"></p>
                </div>

                <p class="form-hint">Isi minimal file atau tautan video. File maksimal 10 MB. Saat edit, file lama tetap disimpan jika tidak memilih pengganti.</p>
                <p class="form-error" id="repository-form-error" role="alert"></p>
            </form>

            <div class="modal__footer">
                <button type="button" class="btn btn--outline" data-close-modal>Batal</button>
                <button type="submit" form="form-repository" class="btn btn--brand" id="repository-simpan">Simpan</button>
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

            const rowsEl = document.getElementById('repository-rows');
            const countEl = document.getElementById('repository-count');
            const searchEl = document.getElementById('repository-search');
            const modal = document.getElementById('repository-modal');
            const form = document.getElementById('form-repository');
            const titleEl = document.getElementById('repository-modal-title');
            const mapelEl = document.getElementById('repository-mapel');
            const mapelIdInput = document.getElementById('repository-mapel-id');
            const judulInput = document.getElementById('repository-judul');
            const deskripsiInput = document.getElementById('repository-deskripsi');
            const fileInput = document.getElementById('repository-file');
            const fileSaatIni = document.getElementById('repository-file-saat-ini');
            const formError = document.getElementById('repository-form-error');
            const simpanButton = document.getElementById('repository-simpan');
            const errorNames = ['mata_pelajaran_id', 'judul', 'deskripsi', 'file'];

            let rows = [];
            let editing = null;
            let saving = false;

            const setError = (name, message) => {
                const element = document.getElementById('error-' + name);
                if (element) element.textContent = message || '';
            };

            const clearErrors = () => {
                errorNames.forEach((name) => setError(name, ''));
                formError.textContent = '';
            };

            const closeModal = () => {
                modal.classList.remove('is-open');
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
                    row.mata_pelajaran && row.mata_pelajaran.nama,
                    row.nama_file,
                    row.pembuat && row.pembuat.name,
                ].some((value) => String(value ?? '').toLowerCase().includes(query)));

                if (!filtered.length) {
                    rowsEl.innerHTML = '<tr><td colspan="6" class="empty-state">Tidak ada data.</td></tr>';
                } else {
                    rowsEl.innerHTML = filtered.map((row, index) => {
                        const id = escapeHtml(row.id);
                        return '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + escapeHtml(row.judul) + '</td>' +
                            '<td>' + escapeHtml((row.mata_pelajaran && row.mata_pelajaran.nama) || 'Belum ditentukan') + '</td>' +
                            '<td>' + escapeHtml(row.nama_file || '-') + '</td>' +
                            '<td>' + escapeHtml((row.pembuat && row.pembuat.name) || 'Tidak tercatat') + '</td>' +
                            '<td><div class="table__actions">' +
                                '<a class="btn btn--sm btn--outline" href="/api/repository/' + id + '/download" title="Unduh" style="color: var(--green-700)">Unduh</a>' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="edit" data-id="' + id + '" title="Edit" style="color: var(--brand-700)">Edit</button>' +
                                '<button type="button" class="btn btn--sm btn--danger" data-action="hapus" data-id="' + id + '" title="Hapus">Hapus</button>' +
                            '</div></td>' +
                        '</tr>';
                    }).join('');
                }

                countEl.textContent = 'Menampilkan ' + filtered.length + ' dari ' + rows.length + ' data';
            };

            const load = async () => {
                document.getElementById('repository-load-error').classList.add('hidden');
                rowsEl.innerHTML = '<tr><td colspan="6" class="empty-state">Memuat data...</td></tr>';
                try {
                    const response = await api('/api/repository');
                    const payload = response?.data?.data ?? response?.data ?? response;
                    rows = Array.isArray(payload) ? payload : [];
                } catch (error) {
                    rows = [];
                    document.getElementById('repository-load-error').classList.remove('hidden');
                }
                render();
            };

            const findRow = (id) => rows.find((row) => String(row.id) === String(id));

            const muatMapel = async () => {
                mapelEl.innerHTML = '<option value="">Pilih mata pelajaran</option>';
                try {
                    const response = await api('/api/mata-pelajaran');
                    const payload = response?.data?.data ?? response?.data ?? response;
                    const items = Array.isArray(payload) ? payload : [];
                    mapelEl.innerHTML += items.map((item) =>
                        '<option value="' + escapeHtml(item.id) + '">' + escapeHtml(item.nama) + '</option>'
                    ).join('');
                    mapelEl.value = mapelIdInput.value;
                } catch (error) {
                    formError.textContent = 'Gagal memuat mata pelajaran.';
                }
            };

            const bukaTambah = () => {
                editing = null;
                form.reset();
                mapelIdInput.value = '';
                fileSaatIni.textContent = '';
                titleEl.textContent = 'Tambah Materi';
                clearErrors();
                modal.classList.add('is-open');
                muatMapel();
            };

            const bukaEdit = (row) => {
                editing = row;
                form.reset();
                mapelIdInput.value = String(row.mata_pelajaran_id ?? '');
                judulInput.value = row.judul ?? '';
                deskripsiInput.value = row.deskripsi ?? '';
                fileSaatIni.textContent = row.nama_asli ? 'File saat ini: ' + row.nama_asli : '';
                titleEl.textContent = 'Edit Materi';
                clearErrors();
                modal.classList.add('is-open');
                muatMapel();
            };

            const hapus = async (id) => {
                if (!confirm('Hapus dokumen ini secara permanen?')) return;
                try {
                    await api('/api/repository/' + id, { method: 'DELETE' });
                    await load();
                } catch (error) {
                    alert(error.data?.message || 'Gagal menghapus dokumen.');
                }
            };

            rowsEl.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const row = findRow(button.dataset.id);
                if (!row) return;
                if (button.dataset.action === 'edit') bukaEdit(row);
                if (button.dataset.action === 'hapus') hapus(row.id);
            });

            mapelEl.addEventListener('change', () => {
                mapelIdInput.value = mapelEl.value;
                setError('mata_pelajaran_id', '');
            });

            const simpan = async (event) => {
                event.preventDefault();
                if (saving) return;

                saving = true;
                clearErrors();
                simpanButton.textContent = 'Menyimpan...';
                simpanButton.disabled = true;

                const payload = new FormData();
                payload.append('mata_pelajaran_id', mapelEl.value);
                payload.append('judul', judulInput.value);
                payload.append('deskripsi', deskripsiInput.value);
                if (fileInput.files && fileInput.files[0]) payload.append('file', fileInput.files[0]);
                if (editing) payload.append('_method', 'PUT');

                try {
                    await api(editing ? '/api/repository/' + editing.id : '/api/repository', {
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
                        formError.textContent = 'Gagal menyimpan materi.';
                    }
                } finally {
                    saving = false;
                    simpanButton.textContent = 'Simpan';
                    simpanButton.disabled = false;
                }
            };

            document.getElementById('btn-tambah-dokumen').addEventListener('click', bukaTambah);
            document.getElementById('repository-coba-lagi').addEventListener('click', load);
            form.addEventListener('submit', simpan);
            searchEl.addEventListener('input', render);

            const kodeForm = document.getElementById('form-kode-akses');
            if (kodeForm) {
                const kodeInput = document.getElementById('kode-akses');
                const simpanKodeButton = document.getElementById('simpan-kode');
                const resetButton = document.getElementById('reset-akses');
                const pesan = document.getElementById('kode-pesan');
                let kodeSaving = false;

                kodeForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (kodeSaving) return;
                    if (!confirm('Simpan kode baru? Akses pengunjung lama akan dibatalkan.')) return;

                    kodeSaving = true;
                    simpanKodeButton.textContent = 'Menyimpan...';
                    simpanKodeButton.disabled = true;
                    resetButton.disabled = true;
                    pesan.textContent = '';

                    try {
                        const response = await api('/api/repository/kode-akses', {
                            method: 'PUT',
                            body: JSON.stringify({ kode: kodeInput.value }),
                            headers: { 'Content-Type': 'application/json' },
                        });
                        kodeInput.value = '';
                        pesan.textContent = response.message || 'Kode disimpan. Akses pengunjung sebelumnya dibatalkan.';
                    } catch (error) {
                        pesan.textContent = error.data?.errors?.kode?.[0]
                            || error.data?.message
                            || 'Gagal menyimpan kode.';
                    } finally {
                        kodeSaving = false;
                        simpanKodeButton.textContent = 'Simpan Kode';
                        simpanKodeButton.disabled = false;
                        resetButton.disabled = false;
                    }
                });

                resetButton.addEventListener('click', async () => {
                    if (kodeSaving) return;
                    if (!confirm('Reset akses seluruh pengunjung? Mereka harus memasukkan kode kembali.')) return;

                    kodeSaving = true;
                    resetButton.textContent = 'Mereset...';
                    resetButton.disabled = true;
                    simpanKodeButton.disabled = true;
                    pesan.textContent = '';

                    try {
                        const response = await api('/api/repository/reset-akses', { method: 'POST' });
                        pesan.textContent = response.message || 'Akses pengunjung berhasil direset. Kode akses tetap sama.';
                    } catch (error) {
                        pesan.textContent = error.data?.message || 'Gagal mereset akses pengunjung.';
                    } finally {
                        kodeSaving = false;
                        resetButton.textContent = 'Reset Akses Pengunjung';
                        resetButton.disabled = false;
                        simpanKodeButton.disabled = false;
                    }
                });
            }

            load();
        })();
    </script>
@endpush
