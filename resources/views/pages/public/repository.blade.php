@extends('layouts.public')

@section('title', 'Repository Sekolah')

@section('content')
    <section class="page-header">
        <div class="page-header__inner">
            <p class="page-header__eyebrow">Sumber Belajar Sekolah</p>
            <h1 class="page-header__title">Repository Sekolah</h1>
            <p class="page-header__description">
                Akses materi pembelajaran dan dokumen pendukung kegiatan sekolah. Gunakan kode akses yang diberikan oleh pihak sekolah untuk membuka repository.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="alert alert--danger mb-4 hidden" id="repository-error" role="alert">
                <span id="repository-error-message"></span>
            </div>

            <p id="repository-loading" class="empty-state" role="status">Memuat repository...</p>

            <form class="card" id="akses-form" hidden>
                <div class="card__body">
                    <x-icon name="lock" class="icon" />
                    <label class="card__title block mt-4" for="kode-akses">Masukkan Kode Akses</label>
                    <p class="text-sm muted mt-2">Mintalah kode kepada pihak sekolah.</p>
                    <input class="input mt-4" id="kode-akses" name="kode" type="password" aria-label="Kode akses repository" autocomplete="off" maxlength="100" placeholder="Kode akses" required>
                    <button class="btn btn--brand btn--block mt-4" id="akses-submit" type="submit">Buka Repository</button>
                </div>
            </form>

            <div id="repository-panel" hidden>
                <div class="filter-bar">
                    <input class="input search-input" id="cari-dokumen" type="search" aria-label="Cari dokumen" placeholder="Cari dokumen...">
                    <button class="btn btn--outline btn--sm" id="keluar-akses" type="button">Keluar</button>
                </div>

                <div class="doc-grid" id="doc-list"></div>
                <p class="empty-state hidden" id="doc-empty">Tidak ada dokumen.</p>
            </div>

            <template id="repository-download-icon"><x-icon name="download" class="icon" /></template>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        async function api(url, options = {}) {
            const res = await fetch(url, { ...options, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', ...(options.headers || {}) } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw Object.assign(new Error(data.message || 'Gagal memuat data.'), { status: res.status, data });
            return data;
        }

        const loading = document.getElementById('repository-loading');
        const errorBox = document.getElementById('repository-error');
        const errorMessage = document.getElementById('repository-error-message');
        const aksesForm = document.getElementById('akses-form');
        const aksesSubmit = document.getElementById('akses-submit');
        const kodeInput = document.getElementById('kode-akses');
        const panel = document.getElementById('repository-panel');
        const searchInput = document.getElementById('cari-dokumen');
        const docList = document.getElementById('doc-list');
        const docEmpty = document.getElementById('doc-empty');
        const downloadUrl = '{{ route('repository.download', ['repository' => '__ID__']) }}';
        const downloadIcon = document.getElementById('repository-download-icon').innerHTML.trim();

        let rows = [];
        let diizinkan = false;
        let saving = false;
        let search = '';

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorBox.classList.toggle('hidden', !message);
        }

        function setView() {
            loading.classList.add('hidden');
            aksesForm.hidden = diizinkan;
            panel.hidden = !diizinkan;
        }

        function renderDocs() {
            const filtered = rows.filter((item) => {
                const nama = item.nama_file || item.nama_asli || '';
                const teks = (item.judul + ' ' + (item.deskripsi || '') + ' ' + nama).toLowerCase();
                return teks.includes(search.toLowerCase());
            });

            docList.innerHTML = filtered.map((item) => {
                const nama = item.nama_file || item.nama_asli || '';
                const href = downloadUrl.replace('__ID__', encodeURIComponent(item.id));
                const ukuran = Number(item.ukuran || 0) / 1024 / 1024;

                return '<article class="doc-card">' +
                    '<div class="doc-card__top">' +
                        '<span class="doc-card__ext">' + escapeHtml(item.ekstensi) + '</span>' +
                        '<h2 class="doc-card__title">' + escapeHtml(item.judul) + '</h2>' +
                    '</div>' +
                    (item.deskripsi ? '<p class="doc-card__desc">' + escapeHtml(item.deskripsi) + '</p>' : '') +
                    '<p class="doc-card__meta">' + escapeHtml(nama) + '</p>' +
                    '<p class="doc-card__meta">' + escapeHtml(ukuran.toFixed(2) + ' MB') + '</p>' +
                    '<a class="btn btn--brand btn--sm mt-4" href="' + escapeHtml(href) + '">' +
                        downloadIcon +
                        'Unduh' +
                    '</a>' +
                '</article>';
            }).join('');

            docList.querySelectorAll('.doc-card__desc').forEach((description) => {
                description.style.whiteSpace = 'pre-wrap';
            });

            docEmpty.classList.toggle('hidden', filtered.length !== 0);
        }

        async function loadRepository() {
            loading.classList.remove('hidden');
            errorBox.classList.add('hidden');
            errorMessage.textContent = '';
            aksesForm.hidden = true;
            panel.hidden = true;

            try {
                const response = await api('/api/repository/publik');
                rows = Array.isArray(response?.data) ? response.data : [];
                diizinkan = true;
                renderDocs();
            } catch (error) {
                rows = [];
                diizinkan = false;

                if (error.status !== 403) {
                    showError('Gagal memuat repository. Silakan coba lagi.');
                }
            } finally {
                setView();
            }
        }

        aksesForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (saving) return;

            saving = true;
            showError('');
            aksesSubmit.textContent = 'Memeriksa...';
            aksesSubmit.disabled = true;

            try {
                await api('/api/repository/akses', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ kode: kodeInput.value }),
                });

                kodeInput.value = '';
                await loadRepository();
            } catch (error) {
                const pesan = error.status === 429
                    ? 'Terlalu banyak percobaan. Tunggu satu menit.'
                    : (error.data?.message || 'Gagal membuka akses.');
                showError(pesan);
            } finally {
                saving = false;
                aksesSubmit.textContent = 'Buka Repository';
                aksesSubmit.disabled = false;
            }
        });

        document.getElementById('keluar-akses').addEventListener('click', async () => {
            try {
                await api('/api/repository/keluar', { method: 'POST' });
                diizinkan = false;
                rows = [];
                search = '';
                searchInput.value = '';
                showError('');
                setView();
            } catch (error) {
                showError('Gagal menutup akses.');
            }
        });

        searchInput.addEventListener('input', () => {
            search = searchInput.value;
            renderDocs();
        });

        void loadRepository();
    </script>
@endpush
