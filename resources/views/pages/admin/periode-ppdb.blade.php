@extends('layouts.admin')

@section('title', 'Periode PPDB')

@section('content')
    <div id="periode-pesan" class="alert alert--success hidden" role="status" style="margin-bottom: 1.5rem">
        <x-icon name="check-circle" />
        <span id="periode-pesan-teks"></span>
    </div>

    <div id="periode-load-error" class="alert alert--danger hidden" role="alert" style="margin-bottom: 1.5rem">
        <x-icon name="alert" />
        <span>Gagal memuat periode PPDB.</span>
        <button type="button" class="btn btn--sm btn--link" id="periode-coba-lagi">Coba lagi</button>
    </div>

    <div class="content__header">
        <div>
            <h1 class="content__title">Periode PPDB</h1>
            <p class="content__description">Kelola jadwal, kuota informasi, dan jumlah peserta per periode</p>
        </div>
        <button type="button" class="btn btn--brand" id="btn-tambah-periode">
            <x-icon name="plus" />
            Tambah Periode
        </button>
    </div>

    <div class="filter-bar">
        <input type="search" id="periode-search" class="input search-input" placeholder="Cari..." aria-label="Cari periode" />
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Periode</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Kuota</th>
                    <th>Pendaftar</th>
                    <th>Diterima</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="periode-rows">
                <tr><td colspan="8" class="empty-state">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
    <p id="periode-count" class="form-hint mt-2">Menampilkan 0 dari 0 data</p>

    <div class="modal" id="periode-modal" role="dialog" aria-modal="true" aria-labelledby="periode-modal-title">
        <div class="modal__dialog">
            <div class="modal__header">
                <h2 class="modal__title" id="periode-modal-title">Tambah Periode PPDB</h2>
                <button type="button" class="btn btn--sm btn--icon btn--ghost" data-close-modal aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>

            <form id="form-periode" class="modal__body stack gap-4">
                <div class="form-field">
                    <label class="label" for="nama-periode">Nama Periode</label>
                    <input id="nama-periode" class="input" type="text" required maxlength="150" placeholder="2027/2028 - Gelombang 1" />
                    <p class="form-error" id="error-nama_periode"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="tanggal-mulai">Tanggal dan Jam Mulai</label>
                    <input id="tanggal-mulai" class="input" type="datetime-local" required />
                    <p class="form-error" id="error-tanggal_mulai"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="tanggal-selesai">Tanggal dan Jam Selesai</label>
                    <input id="tanggal-selesai" class="input" type="datetime-local" required />
                    <p class="form-error" id="error-tanggal_selesai"></p>
                </div>

                <div class="form-field">
                    <label class="label" for="kuota-periode">Kuota Penerimaan</label>
                    <input id="kuota-periode" class="input" type="number" min="0" step="1" placeholder="Kosongkan jika belum ditentukan" />
                    <p class="form-error" id="error-kuota"></p>
                    <p class="form-hint">Kuota hanya sebagai informasi. Tidak membatasi pendaftaran atau penerimaan secara otomatis.</p>
                </div>

                <p class="form-error" id="periode-form-error" role="alert"></p>
            </form>

            <div class="modal__footer">
                <button type="button" class="btn btn--outline" data-close-modal>Batal</button>
                <button type="submit" form="form-periode" class="btn btn--brand" id="periode-simpan">Simpan</button>
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

            const rowsEl = document.getElementById('periode-rows');
            const countEl = document.getElementById('periode-count');
            const searchEl = document.getElementById('periode-search');
            const pesanEl = document.getElementById('periode-pesan');
            const pesanTeks = document.getElementById('periode-pesan-teks');
            const loadErrorEl = document.getElementById('periode-load-error');
            const modal = document.getElementById('periode-modal');
            const form = document.getElementById('form-periode');
            const titleEl = document.getElementById('periode-modal-title');
            const namaInput = document.getElementById('nama-periode');
            const mulaiInput = document.getElementById('tanggal-mulai');
            const selesaiInput = document.getElementById('tanggal-selesai');
            const kuotaInput = document.getElementById('kuota-periode');
            const formError = document.getElementById('periode-form-error');
            const simpanButton = document.getElementById('periode-simpan');
            const errorNames = ['nama_periode', 'tanggal_mulai', 'tanggal_selesai', 'kuota'];

            let rows = [];
            let editing = null;
            let saving = false;
            let deletingId = null;

            function toInputDate(value) {
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';
                const pad = (number) => String(number).padStart(2, '0');
                return [
                    date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()),
                    pad(date.getHours()) + ':' + pad(date.getMinutes()),
                ].join('T');
            }

            function formatDate(value) {
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '-';
                return date.toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }

            const setError = (name, message) => {
                const element = document.getElementById('error-' + name);
                if (element) element.textContent = message || '';
            };

            const clearErrors = () => {
                errorNames.forEach((name) => setError(name, ''));
                formError.textContent = '';
            };

            const showPesan = (message) => {
                pesanTeks.textContent = message;
                pesanEl.classList.remove('hidden');
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
                const filtered = rows.filter((row) =>
                    String(row.nama_periode ?? '').toLowerCase().includes(query)
                );

                if (!filtered.length) {
                    rowsEl.innerHTML = '<tr><td colspan="8" class="empty-state">Tidak ada data.</td></tr>';
                } else {
                    rowsEl.innerHTML = filtered.map((row, index) => {
                        const id = escapeHtml(row.id);
                        const hapusLabel = deletingId === row.id ? 'Menghapus...' : 'Hapus';
                        return '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + escapeHtml(row.nama_periode) + '</td>' +
                            '<td>' + escapeHtml(formatDate(row.tanggal_mulai)) + '</td>' +
                            '<td>' + escapeHtml(formatDate(row.tanggal_selesai)) + '</td>' +
                            '<td>' + escapeHtml(row.kuota ?? 'Belum ditentukan') + '</td>' +
                            '<td>' + escapeHtml(row.pendaftar_count ?? 0) + '</td>' +
                            '<td>' + escapeHtml(row.diterima_count ?? 0) + '</td>' +
                            '<td><div class="table__actions">' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="edit" data-id="' + id + '" title="Edit Periode" style="color: var(--brand-700)">Edit</button>' +
                                '<button type="button" class="btn btn--sm btn--danger" data-action="hapus" data-id="' + id + '" title="Hapus Periode"' + (deletingId !== null ? ' disabled' : '') + '>' + hapusLabel + '</button>' +
                            '</div></td>' +
                        '</tr>';
                    }).join('');
                }

                countEl.textContent = 'Menampilkan ' + filtered.length + ' dari ' + rows.length + ' data';
            };

            const load = async () => {
                loadErrorEl.classList.add('hidden');
                rowsEl.innerHTML = '<tr><td colspan="8" class="empty-state">Memuat data...</td></tr>';
                try {
                    const response = await api('/api/periode-ppdb');
                    const payload = response?.data?.data ?? response?.data ?? response;
                    rows = Array.isArray(payload) ? payload : [];
                } catch (error) {
                    rows = [];
                    loadErrorEl.classList.remove('hidden');
                }
                render();
            };

            const findRow = (id) => rows.find((row) => String(row.id) === String(id));

            const bukaTambah = () => {
                editing = null;
                form.reset();
                clearErrors();
                titleEl.textContent = 'Tambah Periode PPDB';
                modal.classList.add('is-open');
            };

            const bukaEdit = (row) => {
                editing = row;
                form.reset();
                namaInput.value = row.nama_periode ?? '';
                mulaiInput.value = toInputDate(row.tanggal_mulai);
                selesaiInput.value = toInputDate(row.tanggal_selesai);
                kuotaInput.value = row.kuota == null ? '' : String(row.kuota);
                clearErrors();
                titleEl.textContent = 'Edit Periode PPDB';
                modal.classList.add('is-open');
            };

            const simpan = async (event) => {
                event.preventDefault();
                if (saving) return;

                saving = true;
                clearErrors();
                simpanButton.textContent = 'Menyimpan...';
                simpanButton.disabled = true;

                const payload = {
                    nama_periode: namaInput.value.trim(),
                    tanggal_mulai: new Date(mulaiInput.value).toISOString(),
                    tanggal_selesai: new Date(selesaiInput.value).toISOString(),
                    kuota: kuotaInput.value === '' ? null : Number(kuotaInput.value),
                };

                try {
                    await api(editing ? '/api/periode-ppdb/' + editing.id : '/api/periode-ppdb', {
                        method: editing ? 'PUT' : 'POST',
                        body: JSON.stringify(payload),
                        headers: { 'Content-Type': 'application/json' },
                    });
                    modal.classList.remove('is-open');
                    showPesan(editing ? 'Periode berhasil diperbarui.' : 'Periode berhasil ditambahkan.');
                    await load();
                } catch (error) {
                    if (error.status === 422 && error.data?.errors) {
                        Object.entries(error.data.errors).forEach(([name, messages]) => {
                            setError(name, Array.isArray(messages) ? messages[0] : String(messages));
                        });
                    } else {
                        formError.textContent = error.data?.message || 'Gagal menyimpan periode.';
                    }
                } finally {
                    saving = false;
                    simpanButton.textContent = 'Simpan';
                    simpanButton.disabled = false;
                }
            };

            const hapus = async (row) => {
                if (deletingId !== null) return;
                if (!confirm('Hapus periode "' + row.nama_periode + '"?')) return;

                deletingId = row.id;
                pesanEl.classList.add('hidden');
                render();
                try {
                    await api('/api/periode-ppdb/' + row.id, { method: 'DELETE' });
                    deletingId = null;
                    showPesan('Periode berhasil dihapus.');
                    await load();
                } catch (error) {
                    deletingId = null;
                    alert(error.data?.message || 'Gagal menghapus periode.');
                    render();
                }
            };

            rowsEl.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const row = findRow(button.dataset.id);
                if (!row) return;
                if (button.dataset.action === 'edit') bukaEdit(row);
                if (button.dataset.action === 'hapus') hapus(row);
            });

            document.getElementById('btn-tambah-periode').addEventListener('click', bukaTambah);
            document.getElementById('periode-coba-lagi').addEventListener('click', load);
            form.addEventListener('submit', simpan);
            searchEl.addEventListener('input', render);

            load();
        })();
    </script>
@endpush
