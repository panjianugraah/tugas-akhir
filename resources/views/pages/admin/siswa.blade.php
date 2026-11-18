@extends('layouts.admin')

@section('title', 'Data Siswa')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Data Siswa</h1>
            <p class="content__description">Kelola data siswa aktif, lulus, dan mutasi</p>
        </div>
        <button type="button" class="btn btn--brand" id="siswa-add">
            <x-icon name="plus" />
            Tambah Siswa
        </button>
    </div>

    <div id="siswa-page-error" class="alert alert--danger mb-6 hidden" role="alert">
        <x-icon name="alert" />
        <span id="siswa-page-error-text"></span>
        <button type="button" class="btn btn--sm btn--outline" id="siswa-retry">Coba lagi</button>
    </div>

    <div class="toolbar">
        <div class="filter-bar">
            <div class="row gap-2">
                <x-icon name="search" class="muted" />
                <input type="search" id="siswa-search" class="input search-input" placeholder="Cari..." aria-label="Cari data siswa" />
            </div>
            <select id="siswa-kelas-filter" class="select" aria-label="Filter kelas">
                <option value="">Semua Kelas</option>
            </select>
            <select id="siswa-status-filter" class="select" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="lulus">Lulus</option>
                <option value="pindah">Pindah</option>
                <option value="keluar">Keluar</option>
            </select>
        </div>
    </div>

    <div id="siswa-loading" class="empty-state">
        <span class="spinner"></span>
        <p>Memuat data...</p>
    </div>

    <div id="siswa-empty" class="empty-state hidden">
        <x-icon name="user" />
        <p>Tidak ada data.</p>
    </div>

    <div id="siswa-table-wrap" class="table-wrap hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>L/P</th>
                    <th>Kelas</th>
                    <th>No. HP</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="siswa-tbody"></tbody>
        </table>
    </div>

    <p id="siswa-count" class="text-xs muted mt-4">Menampilkan 0 dari 0 data</p>

    <template id="siswa-actions-template">
        <div class="table__actions">
            <button type="button" class="btn btn--icon btn--sm btn--ghost" data-action="edit" title="Edit" aria-label="Edit"><x-icon name="pencil" /></button>
            <button type="button" class="btn btn--icon btn--sm btn--ghost" data-action="graduate" title="Luluskan" aria-label="Luluskan"><x-icon name="graduation-cap" /></button>
            <button type="button" class="btn btn--icon btn--sm btn--ghost" data-action="restore" title="Aktifkan Kembali" aria-label="Aktifkan Kembali"><x-icon name="check-circle" /></button>
            <button type="button" class="btn btn--icon btn--sm btn--danger" data-action="delete" title="Hapus" aria-label="Hapus"><x-icon name="trash" /></button>
        </div>
    </template>

    <div id="siswa-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="siswa-form-title">
        <div class="modal__dialog modal__dialog--wide">
            <div class="modal__header">
                <h2 class="modal__title" id="siswa-form-title">Tambah Siswa</h2>
                <button type="button" class="btn btn--icon btn--ghost" id="siswa-form-close" aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>
            <div class="modal__body">
                <div id="siswa-form-error" class="alert alert--danger mb-4 hidden" role="alert">
                    <x-icon name="alert" />
                    <span id="siswa-form-error-text"></span>
                </div>

                <form id="siswa-form">
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="label label--required" for="siswa-nis">NIS</label>
                            <input id="siswa-nis" name="nis" type="text" class="input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required />
                            <p class="form-error mt-1" data-error-for="nis"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-nisn">NISN</label>
                            <input id="siswa-nisn" name="nisn" type="text" class="input" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" required />
                            <p class="form-error mt-1" data-error-for="nisn"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-nama-lengkap">Nama Lengkap</label>
                            <input id="siswa-nama-lengkap" name="nama_lengkap" type="text" class="input" maxlength="255" required />
                            <p class="form-error mt-1" data-error-for="nama_lengkap"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-jenis-kelamin">Jenis Kelamin</label>
                            <select id="siswa-jenis-kelamin" name="jenis_kelamin" class="select" required>
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <p class="form-error mt-1" data-error-for="jenis_kelamin"></p>
                        </div>

                        <div class="form-field">
                            <label class="label" for="siswa-kelas">Kelas</label>
                            <select id="siswa-kelas" name="kelas_id" class="select">
                                <option value="">Memuat kelas...</option>
                            </select>
                            <p id="siswa-kelas-info" class="form-error mt-1"></p>
                            <p class="form-error mt-1" data-error-for="kelas_id"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-tempat-lahir">Tempat Lahir</label>
                            <input id="siswa-tempat-lahir" name="tempat_lahir" type="text" class="input" maxlength="100" required />
                            <p class="form-error mt-1" data-error-for="tempat_lahir"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-tanggal-lahir">Tanggal Lahir</label>
                            <input id="siswa-tanggal-lahir" name="tanggal_lahir" type="date" class="input" required />
                            <p class="form-error mt-1" data-error-for="tanggal_lahir"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-nama-orang-tua">Nama Orang Tua / Wali</label>
                            <input id="siswa-nama-orang-tua" name="nama_orang_tua" type="text" class="input" maxlength="255" required />
                            <p class="form-error mt-1" data-error-for="nama_orang_tua"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-no-hp">Nomor HP</label>
                            <input id="siswa-no-hp" name="no_hp" type="tel" class="input" maxlength="20" required />
                            <p class="form-error mt-1" data-error-for="no_hp"></p>
                        </div>

                        <div class="form-field">
                            <label class="label" for="siswa-email">Email</label>
                            <input id="siswa-email" name="email" type="email" class="input" maxlength="255" />
                            <p class="form-error mt-1" data-error-for="email"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-status">Status</label>
                            <select id="siswa-status" name="status" class="select" required>
                                <option value="aktif">Aktif</option>
                                <option value="lulus">Lulus</option>
                                <option value="pindah">Pindah</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            <p class="form-error mt-1" data-error-for="status"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="siswa-alamat">Alamat</label>
                            <textarea id="siswa-alamat" name="alamat" class="textarea" rows="3" required></textarea>
                            <p class="form-error mt-1" data-error-for="alamat"></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" id="siswa-form-batal">Batal</button>
                <button type="submit" form="siswa-form" class="btn btn--brand" id="siswa-form-submit">Tambah Siswa</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    async function api(url, options = {}) {
        const res = await fetch(url, { ...options, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', ...(options.headers || {}) } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw Object.assign(new Error(data.message || 'Gagal.'), { status: res.status, data });
        return data;
    }

    const $ = (id) => document.getElementById(id);
    const state = { rows: [], kelas: [], editing: null, loading: false, saving: false, kelasError: false, busyId: null };

    const searchInput = $('siswa-search');
    const kelasFilter = $('siswa-kelas-filter');
    const statusFilter = $('siswa-status-filter');
    const loadingBox = $('siswa-loading');
    const emptyBox = $('siswa-empty');
    const tableWrap = $('siswa-table-wrap');
    const tbody = $('siswa-tbody');
    const count = $('siswa-count');
    const pageError = $('siswa-page-error');
    const pageErrorText = $('siswa-page-error-text');
    const modal = $('siswa-modal');
    const form = $('siswa-form');
    const formTitle = $('siswa-form-title');
    const formError = $('siswa-form-error');
    const formErrorText = $('siswa-form-error-text');
    const closeButton = $('siswa-form-close');
    const batalButton = $('siswa-form-batal');
    const submitButton = $('siswa-form-submit');
    const kelasSelect = $('siswa-kelas');
    const kelasInfo = $('siswa-kelas-info');
    const actionsTemplate = $('siswa-actions-template');

    const escapeMap = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (character) => escapeMap[character]);
    }

    function get(obj, path) {
        return path.split('.').reduce((carry, key) => carry == null ? undefined : carry[key], obj);
    }

    function display(value) {
        return value === null || value === undefined || value === '' ? '-' : escapeHtml(value);
    }

    function extractList(payload) {
        const source = payload && payload.data !== undefined ? payload.data : payload;
        return Array.isArray(source) ? source : [];
    }

    function statusBadge(value) {
        const colors = {
            aktif: 'badge--success',
            diterima: 'badge--success',
            terbit: 'badge--success',
            lulus: 'badge--info',
            menunggu: 'badge--warning',
            pindah: 'badge--warning',
            draft: 'badge--neutral',
            ditolak: 'badge--danger',
            nonaktif: 'badge--danger',
            keluar: 'badge--danger',
        };
        return '<span class="badge ' + (colors[value] || 'badge--neutral') + '">' + escapeHtml(value ?? '') + '</span>';
    }

    function showPageError(message) {
        pageErrorText.textContent = message;
        pageError.classList.remove('hidden');
    }

    function clearPageError() {
        pageErrorText.textContent = '';
        pageError.classList.add('hidden');
    }

    function clearFormErrors() {
        form.querySelectorAll('.form-error').forEach((element) => { element.textContent = ''; });
        form.querySelectorAll('[aria-invalid="true"]').forEach((element) => element.removeAttribute('aria-invalid'));
        formErrorText.textContent = '';
        formError.classList.add('hidden');
    }

    function showFormErrors(payload, message) {
        clearFormErrors();
        const errors = payload && payload.errors ? payload.errors : {};
        Object.keys(errors).forEach((field) => {
            const target = form.querySelector('[data-error-for="' + field + '"]');
            if (target) target.textContent = Array.isArray(errors[field]) ? errors[field][0] : String(errors[field]);
            const input = form.querySelector('[name="' + field + '"]');
            if (input) input.setAttribute('aria-invalid', 'true');
        });
        kelasInfo.textContent = state.kelasError ? 'Gagal memuat pilihan kelas. Tutup dan buka kembali form untuk mencoba lagi.' : '';
        if (message) {
            formErrorText.textContent = message;
            formError.classList.remove('hidden');
        }
    }

    function renderKelasOptions(selected) {
        const options = state.kelas.map((item) => '<option value="' + escapeHtml(item.id) + '">' + escapeHtml(item.nama_kelas) + ' — ' + escapeHtml(item.tahun_ajaran) + '</option>').join('');
        kelasSelect.innerHTML = '<option value="">Belum ditentukan</option>' + options;
        kelasSelect.value = selected || '';
        kelasInfo.textContent = state.kelasError ? 'Gagal memuat pilihan kelas. Tutup dan buka kembali form untuk mencoba lagi.' : '';
        kelasSelect.disabled = state.saving || state.kelasError;
    }

    function filteredRows() {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) return state.rows;
        const keys = ['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'kelas.nama_kelas', 'no_hp', 'status'];
        return state.rows.filter((row) => keys.some((key) => String(get(row, key) ?? '').toLowerCase().includes(query)));
    }

    function render() {
        const rows = filteredRows();
        count.textContent = 'Menampilkan ' + rows.length + ' dari ' + state.rows.length + ' data';

        if (state.loading) {
            loadingBox.classList.remove('hidden');
            emptyBox.classList.add('hidden');
            tableWrap.classList.add('hidden');
            return;
        }

        if (rows.length === 0) {
            loadingBox.classList.add('hidden');
            emptyBox.classList.remove('hidden');
            tableWrap.classList.add('hidden');
            return;
        }

        loadingBox.classList.add('hidden');
        emptyBox.classList.add('hidden');
        tableWrap.classList.remove('hidden');

        tbody.innerHTML = rows.map((row, index) => {
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + display(row.nis) + '</td>' +
                '<td>' + display(row.nisn) + '</td>' +
                '<td>' + display(row.nama_lengkap) + '</td>' +
                '<td>' + display(row.jenis_kelamin) + '</td>' +
                '<td>' + display(get(row, 'kelas.nama_kelas')) + '</td>' +
                '<td>' + display(row.no_hp) + '</td>' +
                '<td>' + statusBadge(row.status) + '</td>' +
                '<td class="text-right" data-actions></td>' +
            '</tr>';
        }).join('');

        const actionCells = tbody.querySelectorAll('[data-actions]');
        rows.forEach((row, index) => {
            const fragment = actionsTemplate.content.cloneNode(true);
            const wrapper = fragment.querySelector('.table__actions');
            wrapper.dataset.id = row.id;
            if (row.status === 'aktif') {
                const restore = fragment.querySelector('[data-action="restore"]');
                if (restore) restore.remove();
            } else {
                const graduate = fragment.querySelector('[data-action="graduate"]');
                if (graduate) graduate.remove();
            }
            actionCells[index].appendChild(fragment);
        });
    }

    async function load() {
        state.loading = true;
        clearPageError();
        render();

        const params = new URLSearchParams();
        if (statusFilter.value) params.set('status', statusFilter.value);
        if (kelasFilter.value) params.set('kelas_id', kelasFilter.value);

        try {
            const payload = await api('/api/siswa' + (params.toString() ? '?' + params.toString() : ''));
            state.rows = extractList(payload);
        } catch (error) {
            state.rows = [];
            showPageError(error.message || 'Gagal memuat data siswa.');
        } finally {
            state.loading = false;
            render();
        }
    }

    async function loadKelas() {
        const selectedFilter = kelasFilter.value;
        kelasSelect.innerHTML = '<option value="">Memuat kelas...</option>';
        kelasFilter.innerHTML = '<option value="">Semua Kelas</option>';
        try {
            const payload = await api('/api/kelas');
            state.kelas = extractList(payload);
            state.kelasError = false;
            renderKelasOptions(state.editing && state.editing.kelas_id != null ? String(state.editing.kelas_id) : '');
            kelasFilter.innerHTML = '<option value="">Semua Kelas</option>' + state.kelas.map((item) => '<option value="' + escapeHtml(item.id) + '">' + escapeHtml(item.nama_kelas) + '</option>').join('');
            kelasFilter.value = selectedFilter;
        } catch (error) {
            state.kelas = [];
            state.kelasError = true;
            renderKelasOptions('');
            kelasFilter.value = selectedFilter;
        } finally {
            refreshControls();
        }
    }

    function refreshControls() {
        form.querySelectorAll('input, select, textarea').forEach((element) => { element.disabled = state.saving; });
        kelasSelect.disabled = state.saving || state.kelasError;
        closeButton.disabled = state.saving;
        batalButton.disabled = state.saving;
        submitButton.disabled = state.saving || state.kelasError;
        submitButton.textContent = state.saving ? 'Menyimpan...' : (state.editing ? 'Simpan Perubahan' : 'Tambah Siswa');
    }

    function fillForm(row) {
        const values = {
            nis: row?.nis ?? '',
            nisn: row?.nisn ?? '',
            nama_lengkap: row?.nama_lengkap ?? '',
            jenis_kelamin: row?.jenis_kelamin ?? '',
            tempat_lahir: row?.tempat_lahir ?? '',
            tanggal_lahir: row?.tanggal_lahir ? String(row.tanggal_lahir).slice(0, 10) : '',
            nama_orang_tua: row?.nama_orang_tua ?? '',
            no_hp: row?.no_hp ?? '',
            email: row?.email ?? '',
            alamat: row?.alamat ?? '',
            status: row?.status ?? 'aktif',
        };
        Object.keys(values).forEach((name) => {
            if (form.elements[name]) form.elements[name].value = values[name];
        });
    }

    function openForm(row) {
        state.editing = row || null;
        clearFormErrors();
        fillForm(row);
        formTitle.textContent = row ? 'Edit Siswa' : 'Tambah Siswa';
        renderKelasOptions(row && row.kelas_id != null ? String(row.kelas_id) : '');
        refreshControls();
        modal.classList.add('is-open');
        if (state.kelasError) loadKelas();
    }

    function closeModal() {
        if (state.saving) return;
        modal.classList.remove('is-open');
    }

    async function changeStatus(row, status) {
        if (state.busyId !== null) return;
        state.busyId = row.id;
        try {
            await api('/api/siswa/' + row.id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ status: status }) });
            await load();
        } catch (error) {
            alert(error.message || 'Gagal mengubah status.');
        } finally {
            state.busyId = null;
        }
    }

    async function hapus(row) {
        if (state.busyId !== null) return;
        if (!confirm('Hapus data siswa ini?')) return;
        state.busyId = row.id;
        try {
            await api('/api/siswa/' + row.id, { method: 'DELETE' });
            await load();
        } catch (error) {
            alert(error.message || 'Gagal menghapus data siswa.');
        } finally {
            state.busyId = null;
        }
    }

    ['nis', 'nisn'].forEach((name) => {
        form.elements[name].addEventListener('input', (event) => {
            event.target.value = event.target.value.replace(/\D/g, '');
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (state.saving || state.kelasError) return;

        state.saving = true;
        clearFormErrors();
        refreshControls();

        const payload = {
            nis: form.elements.nis.value.trim(),
            nisn: form.elements.nisn.value.trim(),
            nama_lengkap: form.elements.nama_lengkap.value.trim(),
            kelas_id: form.elements.kelas_id.value ? Number(form.elements.kelas_id.value) : null,
            jenis_kelamin: form.elements.jenis_kelamin.value,
            tempat_lahir: form.elements.tempat_lahir.value,
            tanggal_lahir: form.elements.tanggal_lahir.value,
            nama_orang_tua: form.elements.nama_orang_tua.value,
            no_hp: form.elements.no_hp.value,
            email: form.elements.email.value.trim() || null,
            alamat: form.elements.alamat.value,
            status: form.elements.status.value,
        };

        try {
            if (state.editing) {
                await api('/api/siswa/' + state.editing.id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            } else {
                await api('/api/siswa', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            }
            modal.classList.remove('is-open');
            await load();
        } catch (error) {
            showFormErrors(error.data, error.message || 'Gagal menyimpan data siswa.');
        } finally {
            state.saving = false;
            refreshControls();
        }
    });

    tbody.addEventListener('click', async (event) => {
        const target = event.target instanceof Element ? event.target : event.target.parentElement;
        const button = target.closest('button[data-action]');
        if (!button || state.busyId !== null) return;
        const wrapper = button.closest('.table__actions');
        const id = Number(wrapper.dataset.id);
        const row = state.rows.find((item) => Number(item.id) === id);
        if (!row) return;

        if (button.dataset.action === 'edit') openForm(row);
        if (button.dataset.action === 'graduate') await changeStatus(row, 'lulus');
        if (button.dataset.action === 'restore') await changeStatus(row, 'aktif');
        if (button.dataset.action === 'delete') await hapus(row);
    });

    $('siswa-add').addEventListener('click', () => openForm(null));
    closeButton.addEventListener('click', closeModal);
    batalButton.addEventListener('click', closeModal);
    $('siswa-retry').addEventListener('click', load);
    searchInput.addEventListener('input', render);
    statusFilter.addEventListener('change', load);
    kelasFilter.addEventListener('change', load);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    loadKelas();
    load();
})();
</script>
@endpush
