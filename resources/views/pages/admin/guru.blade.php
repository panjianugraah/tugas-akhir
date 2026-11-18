@extends('layouts.admin')

@section('title', 'Data Guru')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Data Guru</h1>
            <p class="content__description">Kelola data guru dan tenaga pengajar</p>
        </div>
        <button type="button" class="btn btn--brand" id="guru-add">
            <x-icon name="plus" />
            Tambah Guru
        </button>
    </div>

    <div id="guru-page-error" class="alert alert--danger mb-6 hidden" role="alert">
        <x-icon name="alert" />
        <span id="guru-page-error-text"></span>
        <button type="button" class="btn btn--sm btn--outline" id="guru-retry">Coba lagi</button>
    </div>

    <div class="toolbar">
        <div class="filter-bar">
            <div class="row gap-2">
                <x-icon name="search" class="muted" />
                <input type="search" id="guru-search" class="input search-input" placeholder="Cari..." aria-label="Cari data guru" />
            </div>
            <select id="guru-status-filter" class="select" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <div id="guru-loading" class="empty-state">
        <span class="spinner"></span>
        <p>Memuat data...</p>
    </div>

    <div id="guru-empty" class="empty-state hidden">
        <x-icon name="users" />
        <p>Tidak ada data.</p>
    </div>

    <div id="guru-table-wrap" class="table-wrap hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>L/P</th>
                    <th>Mata Pelajaran</th>
                    <th>No. HP</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="guru-tbody"></tbody>
        </table>
    </div>

    <p id="guru-count" class="text-xs muted mt-4">Menampilkan 0 dari 0 data</p>

    <template id="guru-actions-template">
        <div class="table__actions">
            <button type="button" class="btn btn--icon btn--sm btn--ghost" data-action="edit" title="Edit" aria-label="Edit"><x-icon name="pencil" /></button>
            <button type="button" class="btn btn--icon btn--sm btn--danger" data-action="delete" title="Hapus" aria-label="Hapus"><x-icon name="trash" /></button>
        </div>
    </template>

    <div id="guru-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="guru-form-title">
        <div class="modal__dialog modal__dialog--wide">
            <div class="modal__header">
                <h2 class="modal__title" id="guru-form-title">Tambah Guru</h2>
                <button type="button" class="btn btn--icon btn--ghost" id="guru-form-close" aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>
            <div class="modal__body">
                <div id="guru-form-error" class="alert alert--danger mb-4 hidden" role="alert">
                    <x-icon name="alert" />
                    <span id="guru-form-error-text"></span>
                </div>

                <form id="guru-form">
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="label" for="guru-nip">NIP</label>
                            <input id="guru-nip" name="nip" type="text" class="input" maxlength="18" />
                            <p class="form-error mt-1" data-error-for="nip"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-nama-lengkap">Nama Lengkap</label>
                            <input id="guru-nama-lengkap" name="nama_lengkap" type="text" class="input" maxlength="255" required />
                            <p class="form-error mt-1" data-error-for="nama_lengkap"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-tempat-lahir">Tempat Lahir</label>
                            <input id="guru-tempat-lahir" name="tempat_lahir" type="text" class="input" maxlength="100" required />
                            <p class="form-error mt-1" data-error-for="tempat_lahir"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-tanggal-lahir">Tanggal Lahir</label>
                            <input id="guru-tanggal-lahir" name="tanggal_lahir" type="date" class="input" required />
                            <p class="form-error mt-1" data-error-for="tanggal_lahir"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-mata-pelajaran">Mata Pelajaran</label>
                            <select id="guru-mata-pelajaran" name="mata_pelajaran_id" class="select" required>
                                <option value="">Memuat mata pelajaran...</option>
                            </select>
                            <p id="guru-mapel-info" class="form-error mt-1"></p>
                            <p class="form-error mt-1" data-error-for="mata_pelajaran_id"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-jenis-kelamin">Jenis Kelamin</label>
                            <select id="guru-jenis-kelamin" name="jenis_kelamin" class="select" required>
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <p class="form-error mt-1" data-error-for="jenis_kelamin"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-no-hp">Nomor HP</label>
                            <input id="guru-no-hp" name="no_hp" type="tel" class="input" maxlength="20" required />
                            <p class="form-error mt-1" data-error-for="no_hp"></p>
                        </div>

                        <div class="form-field">
                            <label class="label" for="guru-email">Email</label>
                            <input id="guru-email" name="email" type="email" class="input" maxlength="255" />
                            <p class="form-error mt-1" data-error-for="email"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-status">Status</label>
                            <select id="guru-status" name="status" class="select" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                            <p class="form-error mt-1" data-error-for="status"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="guru-alamat">Alamat</label>
                            <textarea id="guru-alamat" name="alamat" class="textarea" rows="3" required></textarea>
                            <p class="form-error mt-1" data-error-for="alamat"></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" id="guru-form-batal">Batal</button>
                <button type="submit" form="guru-form" class="btn btn--brand" id="guru-form-submit">Tambah Guru</button>
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
    const state = { rows: [], mapel: [], editing: null, loading: false, saving: false, loadingMapel: false, mapelError: '', busyId: null };

    const searchInput = $('guru-search');
    const statusFilter = $('guru-status-filter');
    const loadingBox = $('guru-loading');
    const emptyBox = $('guru-empty');
    const tableWrap = $('guru-table-wrap');
    const tbody = $('guru-tbody');
    const count = $('guru-count');
    const pageError = $('guru-page-error');
    const pageErrorText = $('guru-page-error-text');
    const modal = $('guru-modal');
    const form = $('guru-form');
    const formTitle = $('guru-form-title');
    const formError = $('guru-form-error');
    const formErrorText = $('guru-form-error-text');
    const closeButton = $('guru-form-close');
    const batalButton = $('guru-form-batal');
    const submitButton = $('guru-form-submit');
    const mapelSelect = $('guru-mata-pelajaran');
    const mapelInfo = $('guru-mapel-info');
    const actionsTemplate = $('guru-actions-template');

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

    function setMapelInfo(text, isError) {
        mapelInfo.textContent = text;
        mapelInfo.className = (isError ? 'form-error' : 'form-hint') + ' mt-1';
    }

    function clearFormErrors() {
        form.querySelectorAll('.form-error, .form-hint').forEach((element) => { if (element !== mapelInfo) element.textContent = ''; });
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
        if (message) {
            formErrorText.textContent = message;
            formError.classList.remove('hidden');
        }
    }

    function filteredRows() {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) return state.rows;
        const keys = ['nip', 'nama_lengkap', 'jenis_kelamin', 'mapel.nama', 'no_hp', 'status'];
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
            const mapel = get(row, 'mapel.nama');
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + display(row.nip) + '</td>' +
                '<td>' + display(row.nama_lengkap) + '</td>' +
                '<td>' + display(row.jenis_kelamin) + '</td>' +
                '<td>' + (mapel ? escapeHtml(mapel) : 'Belum ditentukan') + '</td>' +
                '<td>' + display(row.no_hp) + '</td>' +
                '<td>' + statusBadge(row.status) + '</td>' +
                '<td class="text-right" data-actions></td>' +
            '</tr>';
        }).join('');

        const actionCells = tbody.querySelectorAll('[data-actions]');
        rows.forEach((row, index) => {
            const fragment = actionsTemplate.content.cloneNode(true);
            fragment.querySelector('.table__actions').dataset.id = row.id;
            actionCells[index].appendChild(fragment);
        });
    }

    async function load() {
        state.loading = true;
        clearPageError();
        render();

        const params = new URLSearchParams();
        if (statusFilter.value) params.set('status', statusFilter.value);

        try {
            const payload = await api('/api/guru' + (params.toString() ? '?' + params.toString() : ''));
            state.rows = extractList(payload);
        } catch (error) {
            state.rows = [];
            showPageError(error.message || 'Gagal memuat data guru.');
        } finally {
            state.loading = false;
            render();
        }
    }

    function renderMapelOptions(selected) {
        const options = state.mapel.map((item) => '<option value="' + escapeHtml(item.id) + '">' + escapeHtml(item.nama) + '</option>').join('');
        mapelSelect.innerHTML = '<option value="">-- Pilih Mata Pelajaran --</option>' + options;
        mapelSelect.value = selected || '';
    }

    async function loadMapel(selected) {
        state.loadingMapel = true;
        state.mapel = [];
        state.mapelError = '';
        setMapelInfo('');
        mapelSelect.innerHTML = '<option value="">Memuat mata pelajaran...</option>';
        refreshControls();

        try {
            const payload = await api('/api/mata-pelajaran');
            state.mapel = extractList(payload);
            renderMapelOptions(selected);
            if (state.mapel.length === 0) setMapelInfo('Belum ada mata pelajaran. Tambahkan data mata pelajaran terlebih dahulu.', false);
        } catch (error) {
            state.mapelError = 'Gagal memuat mata pelajaran.';
            renderMapelOptions('');
            setMapelInfo(state.mapelError + ' Tutup dan buka kembali form untuk mencoba lagi.', true);
        } finally {
            state.loadingMapel = false;
            refreshControls();
        }
    }

    function refreshControls() {
        form.querySelectorAll('input, select, textarea').forEach((element) => { element.disabled = state.saving; });
        mapelSelect.disabled = state.saving || state.loadingMapel || state.mapelError !== '';
        closeButton.disabled = state.saving;
        batalButton.disabled = state.saving;
        submitButton.disabled = state.saving || state.loadingMapel || state.mapelError !== '' || state.mapel.length === 0;
        submitButton.textContent = state.saving ? 'Menyimpan...' : (state.editing ? 'Simpan Perubahan' : 'Tambah Guru');
    }

    function fillForm(row) {
        const values = {
            nip: row?.nip ?? '',
            nama_lengkap: row?.nama_lengkap ?? '',
            tempat_lahir: row?.tempat_lahir ?? '',
            tanggal_lahir: row?.tanggal_lahir ? String(row.tanggal_lahir).slice(0, 10) : '',
            no_hp: row?.no_hp ?? '',
            email: row?.email ?? '',
            alamat: row?.alamat ?? '',
            status: row?.status ?? 'aktif',
            jenis_kelamin: row?.jenis_kelamin ?? '',
        };
        Object.keys(values).forEach((name) => {
            if (form.elements[name]) form.elements[name].value = values[name];
        });
    }

    function openForm(row) {
        state.editing = row || null;
        clearFormErrors();
        fillForm(row);
        formTitle.textContent = row ? 'Edit Guru' : 'Tambah Guru';
        refreshControls();
        modal.classList.add('is-open');
        loadMapel(row && row.mata_pelajaran_id != null ? String(row.mata_pelajaran_id) : '');
    }

    function closeModal() {
        if (state.saving) return;
        modal.classList.remove('is-open');
    }

    async function hapus(row) {
        if (state.busyId !== null) return;
        if (!confirm('Hapus data guru ini?')) return;
        state.busyId = row.id;
        try {
            await api('/api/guru/' + row.id, { method: 'DELETE' });
            await load();
        } catch (error) {
            alert(error.message || 'Gagal menghapus data guru.');
        } finally {
            state.busyId = null;
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (state.saving || state.loadingMapel || state.mapelError || state.mapel.length === 0) return;

        state.saving = true;
        clearFormErrors();
        refreshControls();

        const payload = {
            nip: form.elements.nip.value.trim() || null,
            nama_lengkap: form.elements.nama_lengkap.value.trim(),
            jenis_kelamin: form.elements.jenis_kelamin.value,
            tempat_lahir: form.elements.tempat_lahir.value,
            tanggal_lahir: form.elements.tanggal_lahir.value,
            mata_pelajaran_id: Number(form.elements.mata_pelajaran_id.value),
            no_hp: form.elements.no_hp.value,
            email: form.elements.email.value.trim() || null,
            alamat: form.elements.alamat.value,
            status: form.elements.status.value,
        };

        try {
            if (state.editing) {
                await api('/api/guru/' + state.editing.id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            } else {
                await api('/api/guru', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            }
            modal.classList.remove('is-open');
            await load();
        } catch (error) {
            showFormErrors(error.data, error.message || 'Gagal menyimpan data guru.');
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
        if (button.dataset.action === 'delete') await hapus(row);
    });

    $('guru-add').addEventListener('click', () => openForm(null));
    closeButton.addEventListener('click', closeModal);
    batalButton.addEventListener('click', closeModal);
    $('guru-retry').addEventListener('click', load);
    searchInput.addEventListener('input', render);
    statusFilter.addEventListener('change', load);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    load();
})();
</script>
@endpush
