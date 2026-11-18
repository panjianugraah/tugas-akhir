@extends('layouts.admin')

@section('title', 'Data Kelas')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Data Kelas</h1>
            <p class="content__description">Kelola kelas dan wali kelas sekolah</p>
        </div>
        <button type="button" class="btn btn--brand" id="kelas-add">
            <x-icon name="plus" />
            Tambah Kelas
        </button>
    </div>

    <div id="kelas-page-error" class="alert alert--danger mb-6 hidden" role="alert">
        <x-icon name="alert" />
        <span id="kelas-page-error-text"></span>
        <button type="button" class="btn btn--sm btn--outline" id="kelas-retry">Coba lagi</button>
    </div>

    <div class="toolbar">
        <div class="filter-bar">
            <div class="row gap-2">
                <x-icon name="search" class="muted" />
                <input type="search" id="kelas-search" class="input search-input" placeholder="Cari..." aria-label="Cari data kelas" />
            </div>
        </div>
    </div>

    <div id="kelas-loading" class="empty-state">
        <span class="spinner"></span>
        <p>Memuat data...</p>
    </div>

    <div id="kelas-empty" class="empty-state hidden">
        <x-icon name="building" />
        <p>Tidak ada data.</p>
    </div>

    <div id="kelas-table-wrap" class="table-wrap hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Tahun Ajaran</th>
                    <th>Kapasitas</th>
                    <th>Wali Kelas</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="kelas-tbody"></tbody>
        </table>
    </div>

    <p id="kelas-count" class="text-xs muted mt-4">Menampilkan 0 dari 0 data</p>

    <template id="kelas-actions-template">
        <div class="table__actions">
            <button type="button" class="btn btn--icon btn--sm btn--ghost" data-action="edit" title="Edit" aria-label="Edit"><x-icon name="pencil" /></button>
            <button type="button" class="btn btn--icon btn--sm btn--danger" data-action="delete" title="Hapus" aria-label="Hapus"><x-icon name="trash" /></button>
        </div>
    </template>

    <div id="kelas-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="kelas-form-title">
        <div class="modal__dialog">
            <div class="modal__header">
                <h2 class="modal__title" id="kelas-form-title">Tambah Kelas</h2>
                <button type="button" class="btn btn--icon btn--ghost" id="kelas-form-close" aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>
            <div class="modal__body">
                <div id="kelas-form-error" class="alert alert--danger mb-4 hidden" role="alert">
                    <x-icon name="alert" />
                    <span id="kelas-form-error-text"></span>
                </div>

                <form id="kelas-form">
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="label label--required" for="kelas-nama">Nama Kelas</label>
                            <input id="kelas-nama" name="nama_kelas" type="text" class="input" placeholder="Contoh: VII A" maxlength="50" required />
                            <p class="form-error mt-1" data-error-for="nama_kelas"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="kelas-tingkat">Tingkat</label>
                            <select id="kelas-tingkat" name="tingkat" class="select" required>
                                <option value="7">VII</option>
                                <option value="8">VIII</option>
                                <option value="9">IX</option>
                            </select>
                            <p class="form-error mt-1" data-error-for="tingkat"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="kelas-tahun-ajaran">Tahun Ajaran</label>
                            <input id="kelas-tahun-ajaran" name="tahun_ajaran" type="text" class="input" placeholder="2027/2028" maxlength="9" required />
                            <p class="form-error mt-1" data-error-for="tahun_ajaran"></p>
                        </div>

                        <div class="form-field">
                            <label class="label label--required" for="kelas-kapasitas">Kapasitas</label>
                            <input id="kelas-kapasitas" name="kapasitas" type="number" class="input" min="1" value="32" required />
                            <p class="form-error mt-1" data-error-for="kapasitas"></p>
                        </div>

                        <div class="form-field">
                            <label class="label" for="kelas-wali">Wali Kelas</label>
                            <select id="kelas-wali" name="wali_kelas_id" class="select">
                                <option value="">Memuat guru...</option>
                            </select>
                            <p id="kelas-guru-info" class="form-error mt-1"></p>
                            <p class="form-error mt-1" data-error-for="wali_kelas_id"></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" id="kelas-form-batal">Batal</button>
                <button type="submit" form="kelas-form" class="btn btn--brand" id="kelas-form-submit">Tambah Kelas</button>
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
    const state = { rows: [], guru: [], editing: null, loading: false, saving: false, loadingGuru: false, guruError: '', busyId: null };

    const searchInput = $('kelas-search');
    const loadingBox = $('kelas-loading');
    const emptyBox = $('kelas-empty');
    const tableWrap = $('kelas-table-wrap');
    const tbody = $('kelas-tbody');
    const count = $('kelas-count');
    const pageError = $('kelas-page-error');
    const pageErrorText = $('kelas-page-error-text');
    const modal = $('kelas-modal');
    const form = $('kelas-form');
    const formTitle = $('kelas-form-title');
    const formError = $('kelas-form-error');
    const formErrorText = $('kelas-form-error-text');
    const closeButton = $('kelas-form-close');
    const batalButton = $('kelas-form-batal');
    const submitButton = $('kelas-form-submit');
    const guruSelect = $('kelas-wali');
    const guruInfo = $('kelas-guru-info');
    const actionsTemplate = $('kelas-actions-template');

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

    function showPageError(message) {
        pageErrorText.textContent = message;
        pageError.classList.remove('hidden');
    }

    function clearPageError() {
        pageErrorText.textContent = '';
        pageError.classList.add('hidden');
    }

    function clearFormErrors() {
        form.querySelectorAll('.form-error, .form-hint').forEach((element) => { if (element !== guruInfo) element.textContent = ''; });
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
        const keys = ['nama_kelas', 'tingkat', 'tahun_ajaran', 'kapasitas', 'wali_kelas.nama_lengkap', 'waliKelas.nama_lengkap'];
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
            const wali = get(row, 'wali_kelas.nama_lengkap') || get(row, 'waliKelas.nama_lengkap');
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + display(row.nama_kelas) + '</td>' +
                '<td>' + display(row.tingkat) + '</td>' +
                '<td>' + display(row.tahun_ajaran) + '</td>' +
                '<td>' + display(row.kapasitas) + '</td>' +
                '<td>' + (wali ? escapeHtml(wali) : 'Belum ditentukan') + '</td>' +
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

        try {
            const payload = await api('/api/kelas');
            state.rows = extractList(payload);
        } catch (error) {
            state.rows = [];
            showPageError(error.message || 'Gagal memuat data kelas.');
        } finally {
            state.loading = false;
            render();
        }
    }

    function renderGuruOptions(selected) {
        const options = state.guru.map((item) => '<option value="' + escapeHtml(item.id) + '">' + escapeHtml(item.nama_lengkap) + '</option>').join('');
        guruSelect.innerHTML = '<option value="">Belum ditentukan</option>' + options;
        guruSelect.value = selected || '';
    }

    async function loadGuru(selected) {
        state.loadingGuru = true;
        state.guru = [];
        state.guruError = '';
        guruInfo.textContent = '';
        guruSelect.innerHTML = '<option value="">Memuat guru...</option>';
        refreshControls();

        try {
            const payload = await api('/api/guru');
            state.guru = extractList(payload);
            renderGuruOptions(selected);
        } catch (error) {
            state.guruError = 'Gagal memuat pilihan wali kelas.';
            renderGuruOptions('');
            guruInfo.textContent = state.guruError + ' Tutup dan buka kembali form untuk mencoba lagi.';
        } finally {
            state.loadingGuru = false;
            refreshControls();
        }
    }

    function refreshControls() {
        form.querySelectorAll('input, select, textarea').forEach((element) => { element.disabled = state.saving; });
        guruSelect.disabled = state.saving || state.loadingGuru || state.guruError !== '';
        closeButton.disabled = state.saving;
        batalButton.disabled = state.saving;
        submitButton.disabled = state.saving || state.loadingGuru || state.guruError !== '';
        submitButton.textContent = state.saving ? 'Menyimpan...' : (state.editing ? 'Simpan Perubahan' : 'Tambah Kelas');
    }

    function fillForm(row) {
        const values = {
            nama_kelas: row?.nama_kelas ?? '',
            tingkat: row?.tingkat != null ? String(row.tingkat) : '7',
            tahun_ajaran: row?.tahun_ajaran ?? '',
            kapasitas: row?.kapasitas != null ? String(row.kapasitas) : '32',
        };
        Object.keys(values).forEach((name) => {
            if (form.elements[name]) form.elements[name].value = values[name];
        });
    }

    function openForm(row) {
        state.editing = row || null;
        clearFormErrors();
        fillForm(row);
        formTitle.textContent = row ? 'Edit Kelas' : 'Tambah Kelas';
        refreshControls();
        modal.classList.add('is-open');
        loadGuru(row && row.wali_kelas_id != null ? String(row.wali_kelas_id) : '');
    }

    function closeModal() {
        if (state.saving) return;
        modal.classList.remove('is-open');
    }

    async function hapus(row) {
        if (state.busyId !== null) return;
        if (!confirm('Hapus data kelas ini?')) return;
        state.busyId = row.id;
        try {
            await api('/api/kelas/' + row.id, { method: 'DELETE' });
            await load();
        } catch (error) {
            alert(error.message || 'Gagal menghapus kelas.');
        } finally {
            state.busyId = null;
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (state.saving || state.loadingGuru || state.guruError) return;

        state.saving = true;
        clearFormErrors();
        refreshControls();

        const payload = {
            nama_kelas: form.elements.nama_kelas.value.trim(),
            tingkat: Number(form.elements.tingkat.value),
            tahun_ajaran: form.elements.tahun_ajaran.value.trim(),
            kapasitas: Number(form.elements.kapasitas.value),
            wali_kelas_id: form.elements.wali_kelas_id.value ? Number(form.elements.wali_kelas_id.value) : null,
        };

        try {
            if (state.editing) {
                await api('/api/kelas/' + state.editing.id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            } else {
                await api('/api/kelas', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            }
            modal.classList.remove('is-open');
            await load();
        } catch (error) {
            showFormErrors(error.data, error.message || 'Gagal menyimpan data kelas.');
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

    $('kelas-add').addEventListener('click', () => openForm(null));
    closeButton.addEventListener('click', closeModal);
    batalButton.addEventListener('click', closeModal);
    $('kelas-retry').addEventListener('click', load);
    searchInput.addEventListener('input', render);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    load();
})();
</script>
@endpush
