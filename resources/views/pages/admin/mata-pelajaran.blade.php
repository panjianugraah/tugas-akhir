@extends('layouts.admin')

@section('title', 'Mata Pelajaran')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Mata Pelajaran</h1>
            <p class="content__description">Kelola mata pelajaran untuk data guru dan materi repository.</p>
        </div>
        <button type="button" class="btn btn--brand" id="mapel-add">
            <x-icon name="plus" />
            Tambah Mata Pelajaran
        </button>
    </div>

    <div id="mapel-page-error" class="alert alert--danger mb-6 hidden" role="alert">
        <x-icon name="alert" />
        <span id="mapel-page-error-text"></span>
        <button type="button" class="btn btn--sm btn--outline" id="mapel-retry">Coba lagi</button>
    </div>

    <div class="toolbar">
        <div class="filter-bar">
            <div class="row gap-2">
                <x-icon name="search" class="muted" />
                <input type="search" id="mapel-search" class="input search-input" placeholder="Cari..." aria-label="Cari data mata pelajaran" />
            </div>
        </div>
    </div>

    <div id="mapel-loading" class="empty-state">
        <span class="spinner"></span>
        <p>Memuat data...</p>
    </div>

    <div id="mapel-empty" class="empty-state hidden">
        <x-icon name="book" />
        <p>Tidak ada data.</p>
    </div>

    <div id="mapel-table-wrap" class="table-wrap hidden">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Jumlah Guru</th>
                    <th>Jumlah Materi</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="mapel-tbody"></tbody>
        </table>
    </div>

    <p id="mapel-count" class="text-xs muted mt-4">Menampilkan 0 dari 0 data</p>

    <template id="mapel-actions-template">
        <div class="table__actions">
            <button type="button" class="btn btn--icon btn--sm btn--ghost" data-action="edit" title="Edit" aria-label="Edit"><x-icon name="pencil" /></button>
            <button type="button" class="btn btn--icon btn--sm btn--danger" data-action="delete" title="Hapus" aria-label="Hapus"><x-icon name="trash" /></button>
        </div>
    </template>

    <div id="mapel-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="mapel-form-title">
        <div class="modal__dialog">
            <div class="modal__header">
                <h2 class="modal__title" id="mapel-form-title">Tambah Mata Pelajaran</h2>
                <button type="button" class="btn btn--icon btn--ghost" id="mapel-form-close" aria-label="Tutup form">
                    <x-icon name="x" />
                </button>
            </div>
            <div class="modal__body">
                <div id="mapel-form-error" class="alert alert--danger mb-4 hidden" role="alert">
                    <x-icon name="alert" />
                    <span id="mapel-form-error-text"></span>
                </div>

                <form id="mapel-form">
                    <div class="form-field">
                        <label class="label label--required" for="mapel-nama">Nama Mata Pelajaran</label>
                        <input id="mapel-nama" name="nama" type="text" class="input" placeholder="Contoh: Matematika" maxlength="100" required aria-describedby="mapel-nama-error" />
                        <p id="mapel-nama-error" class="form-error mt-1" data-error-for="nama"></p>
                    </div>
                </form>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" id="mapel-form-batal">Batal</button>
                <button type="submit" form="mapel-form" class="btn btn--brand" id="mapel-form-submit">Simpan</button>
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
    const state = { rows: [], editing: null, loading: false, saving: false, busyId: null };

    const searchInput = $('mapel-search');
    const loadingBox = $('mapel-loading');
    const emptyBox = $('mapel-empty');
    const tableWrap = $('mapel-table-wrap');
    const tbody = $('mapel-tbody');
    const count = $('mapel-count');
    const pageError = $('mapel-page-error');
    const pageErrorText = $('mapel-page-error-text');
    const modal = $('mapel-modal');
    const form = $('mapel-form');
    const formTitle = $('mapel-form-title');
    const formError = $('mapel-form-error');
    const formErrorText = $('mapel-form-error-text');
    const closeButton = $('mapel-form-close');
    const batalButton = $('mapel-form-batal');
    const submitButton = $('mapel-form-submit');
    const namaInput = $('mapel-nama');
    const actionsTemplate = $('mapel-actions-template');

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
            if (field === 'nama') namaInput.setAttribute('aria-invalid', 'true');
        });
        if (message) {
            formErrorText.textContent = message;
            formError.classList.remove('hidden');
        }
    }

    function filteredRows() {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) return state.rows;
        const keys = ['nama', 'guru_count', 'materi_count'];
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
                '<td>' + display(row.nama) + '</td>' +
                '<td>' + display(row.guru_count) + '</td>' +
                '<td>' + display(row.materi_count) + '</td>' +
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
            const payload = await api('/api/mata-pelajaran');
            state.rows = extractList(payload);
        } catch (error) {
            state.rows = [];
            showPageError(error.message || 'Gagal memuat mata pelajaran.');
        } finally {
            state.loading = false;
            render();
        }
    }

    function refreshControls() {
        namaInput.disabled = state.saving;
        closeButton.disabled = state.saving;
        batalButton.disabled = state.saving;
        submitButton.disabled = state.saving;
        submitButton.textContent = state.saving ? 'Menyimpan...' : 'Simpan';
    }

    function openForm(row) {
        state.editing = row || null;
        clearFormErrors();
        namaInput.value = row?.nama ?? '';
        formTitle.textContent = row ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran';
        refreshControls();
        modal.classList.add('is-open');
        namaInput.focus();
    }

    function closeModal() {
        if (state.saving) return;
        modal.classList.remove('is-open');
    }

    async function hapus(row) {
        if (state.busyId !== null) return;
        if (!confirm('Hapus mata pelajaran "' + row.nama + '"?')) return;
        state.busyId = row.id;
        try {
            await api('/api/mata-pelajaran/' + row.id, { method: 'DELETE' });
            await load();
        } catch (error) {
            alert(error.message || 'Gagal menghapus mata pelajaran.');
        } finally {
            state.busyId = null;
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (state.saving) return;

        const namaBersih = namaInput.value.trim();
        if (!namaBersih) {
            showFormErrors({ nama: ['Nama mata pelajaran wajib diisi.'] }, '');
            return;
        }

        state.saving = true;
        clearFormErrors();
        refreshControls();

        try {
            if (state.editing) {
                await api('/api/mata-pelajaran/' + state.editing.id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nama: namaBersih }) });
            } else {
                await api('/api/mata-pelajaran', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nama: namaBersih }) });
            }
            modal.classList.remove('is-open');
            await load();
        } catch (error) {
            showFormErrors(error.data, error.message || 'Gagal menyimpan mata pelajaran.');
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

    $('mapel-add').addEventListener('click', () => openForm(null));
    closeButton.addEventListener('click', closeModal);
    batalButton.addEventListener('click', closeModal);
    $('mapel-retry').addEventListener('click', load);
    searchInput.addEventListener('input', render);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    load();
})();
</script>
@endpush
