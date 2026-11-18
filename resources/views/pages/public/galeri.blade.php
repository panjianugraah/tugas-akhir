@extends('layouts.public')

@section('title', 'Galeri')

@section('content')
    <section class="page-header">
        <div class="page-header__inner">
            <p class="page-header__eyebrow">Dokumentasi Sekolah</p>
            <h1 class="page-header__title">Galeri Sekolah</h1>
            <p class="page-header__description">
                Dokumentasi kegiatan, prestasi, dan momen kebersamaan keluarga besar SMP Angkasa Adi Soemarmo.
            </p>
        </div>
    </section>

    <section class="section section-anchor" id="gallery">
        <div class="container container--wide">
            <p id="galeri-loading" class="empty-state" role="status">Memuat galeri...</p>

            <div id="galeri-error" class="text-center py-12 hidden">
                <p class="form-error" role="alert" id="galeri-error-message"></p>
                <button type="button" class="btn btn--brand mt-4" id="galeri-retry">Coba Lagi</button>
            </div>

            <p id="galeri-empty" class="empty-state hidden">Belum ada foto galeri.</p>

            <div id="galeri-list" class="gallery-grid hidden"></div>

            <template id="galeri-image-icon"><x-icon name="image-off" class="icon" /></template>
        </div>
    </section>

    <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Foto kegiatan sekolah">
        <button type="button" class="lightbox__close" id="lightbox-close" aria-label="Tutup gambar">
            <x-icon name="x" />
        </button>
        <img id="lightbox-image" alt="">
        <p class="lightbox__caption" id="lightbox-caption"></p>
    </div>
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

        const loading = document.getElementById('galeri-loading');
        const errorBox = document.getElementById('galeri-error');
        const errorMessage = document.getElementById('galeri-error-message');
        const emptyBox = document.getElementById('galeri-empty');
        const list = document.getElementById('galeri-list');
        const imageIcon = document.getElementById('galeri-image-icon').innerHTML.trim();
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxCaption = document.getElementById('lightbox-caption');
        const lightboxClose = document.getElementById('lightbox-close');

        let rows = [];
        let percobaan = 0;
        let gambarTerpilih = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        function closeLightbox() {
            gambarTerpilih = null;
            lightbox.classList.remove('is-open');
        }

        function openLightbox(id) {
            const image = rows.find((item) => String(item.id) === String(id));
            if (!image || !image.gambar_url) return;

            gambarTerpilih = image;
            lightboxImage.src = image.gambar_url;
            lightboxImage.alt = image.deskripsi || 'Foto kegiatan sekolah';
            lightboxCaption.textContent = image.deskripsi || '';
            lightbox.setAttribute('aria-label', image.deskripsi || 'Foto kegiatan sekolah');
            lightbox.classList.add('is-open');
        }

        async function loadGaleri() {
            loading.classList.remove('hidden');
            errorBox.classList.add('hidden');
            emptyBox.classList.add('hidden');
            list.classList.add('hidden');

            try {
                const response = await api('/api/galeri');
                rows = Array.isArray(response?.data) ? response.data : [];

                if (rows.length === 0) {
                    emptyBox.classList.remove('hidden');
                    return;
                }

                list.innerHTML = rows.map((image) => {
                    const deskripsi = image.deskripsi || '';
                    const alt = escapeHtml(deskripsi || 'Foto kegiatan sekolah');
                    const gambar = image.gambar_url
                        ? '<button type="button" data-galeri-id="' + escapeHtml(image.id) + '" aria-label="Lihat gambar penuh: ' + alt + '">' +
                            '<img src="' + escapeHtml(image.gambar_url) + '" alt="' + alt + '" loading="lazy">' +
                          '</button>'
                        : '<div class="gallery-placeholder">' + imageIcon + '</div>';

                    return '<figure class="gallery-card">' +
                        gambar +
                        (deskripsi ? '<figcaption class="gallery-card__caption">' + escapeHtml(deskripsi) + '</figcaption>' : '') +
                    '</figure>';
                }).join('');

                list.querySelectorAll('.gallery-card__caption').forEach((caption) => {
                    caption.style.whiteSpace = 'pre-wrap';
                });

                list.classList.remove('hidden');
            } catch (error) {
                errorMessage.textContent = 'Gagal memuat galeri. Silakan coba lagi.';
                errorBox.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }

        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-galeri-id]');
            if (button) openLightbox(button.dataset.galeriId);
        });

        document.getElementById('galeri-retry').addEventListener('click', () => {
            percobaan += 1;
            void loadGaleri();
        });

        lightboxClose.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) closeLightbox();
        });
        lightboxImage.addEventListener('click', (event) => event.stopPropagation());
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeLightbox();
        });

        void loadGaleri();
    </script>
@endpush
