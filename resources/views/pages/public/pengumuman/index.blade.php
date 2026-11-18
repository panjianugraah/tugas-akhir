@extends('layouts.public')

@section('title', 'Pengumuman Sekolah')

@section('content')
    <section class="page-header">
        <div class="page-header__inner">
            <p class="page-header__eyebrow">Informasi Sekolah</p>
            <h1 class="page-header__title">Pengumuman Sekolah</h1>
            <p class="page-header__description">
                Temukan informasi terbaru mengenai kegiatan akademik, penerimaan peserta didik baru, dan pemberitahuan resmi SMP Angkasa Adi Soemarmo.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <p id="pengumuman-loading" class="empty-state" role="status">Memuat pengumuman...</p>

            <template id="pengumuman-image-icon"><x-icon name="image-off" class="icon" /></template>

            <div id="pengumuman-error" class="alert alert--danger mt-8 hidden" role="alert">
                <span id="pengumuman-error-message"></span>
            </div>

            <p id="pengumuman-empty" class="empty-state hidden">Belum ada pengumuman.</p>

            <div id="pengumuman-list" class="announcement-grid hidden"></div>
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

        const loading = document.getElementById('pengumuman-loading');
        const errorBox = document.getElementById('pengumuman-error');
        const errorMessage = document.getElementById('pengumuman-error-message');
        const emptyBox = document.getElementById('pengumuman-empty');
        const list = document.getElementById('pengumuman-list');
        const detailUrl = '{{ route('pengumuman.publik.show', ['slug' => '__SLUG__']) }}';
        const imageIcon = document.getElementById('pengumuman-image-icon').innerHTML.trim();

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        async function loadPengumuman() {
            loading.classList.remove('hidden');
            errorBox.classList.add('hidden');
            emptyBox.classList.add('hidden');
            list.classList.add('hidden');

            try {
                const response = await api('/api/pengumuman/publik');
                const rows = Array.isArray(response?.data) ? response.data : [];

                if (rows.length === 0) {
                    emptyBox.classList.remove('hidden');
                    return;
                }

                list.innerHTML = rows.map((item) => {
                    const judul = escapeHtml(item.judul);
                    const gambar = item.gambar_url
                        ? '<img class="announcement-card__image" src="' + escapeHtml(item.gambar_url) + '" alt="' + judul + '" loading="lazy">'
                        : '<div class="gallery-placeholder">' + imageIcon + '</div>';
                    const href = detailUrl.replace('__SLUG__', encodeURIComponent(item.slug));

                    return '<a class="announcement-card" href="' + escapeHtml(href) + '">' +
                        gambar +
                        '<div class="announcement-card__body">' +
                            '<h2 class="announcement-card__title">' + judul + '</h2>' +
                            '<p class="announcement-card__excerpt">' + escapeHtml(item.isi) + '</p>' +
                            '<p class="announcement-card__link">Baca selengkapnya →</p>' +
                        '</div>' +
                    '</a>';
                }).join('');

                list.classList.remove('hidden');
            } catch (error) {
                errorMessage.textContent = 'Gagal memuat pengumuman. Silakan coba lagi.';
                errorBox.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }

        void loadPengumuman();
    </script>
@endpush
