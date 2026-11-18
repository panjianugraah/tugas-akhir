<section id="gallery" class="section section--soft section-anchor">
    <div class="container">
        <div class="text-center mb-12">
            <h1 class="section-title">Galeri</h1>
            <p class="text-lg muted">Galeri seputar penghargaan maupun aktivitas yang dilaksanakan di SMP Angkasa Adi Soemarmo.</p>
        </div>

        <div id="gallery-state" class="empty-state">
            <span class="spinner" aria-hidden="true"></span>
            <p class="mt-4">Memuat galeri...</p>
        </div>
        <div id="gallery-grid" class="gallery-grid hidden"></div>

        <template id="gallery-placeholder-template">
            <div class="gallery-placeholder">
                <x-icon name="image-off" />
            </div>
        </template>
    </div>
</section>

<div id="gallery-lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Foto kegiatan sekolah">
    <button type="button" class="lightbox__close" data-lightbox-close aria-label="Tutup gambar">
        <x-icon name="x" />
    </button>
    <img data-lightbox-image alt="" class="hidden" />
    <p class="lightbox__caption" data-lightbox-caption></p>
</div>

@push('scripts')
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            async function api(url, options = {}) { const res = await fetch(url, { ...options, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', ...(options.headers || {}) } }); const data = await res.json().catch(() => ({})); if (!res.ok) throw Object.assign(new Error(data.message || 'Gagal memuat data.'), { status: res.status, data }); return data; }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char]));
            }

            const state = document.getElementById('gallery-state');
            const grid = document.getElementById('gallery-grid');
            const lightbox = document.getElementById('gallery-lightbox');
            const lightboxImage = lightbox.querySelector('[data-lightbox-image]');
            const lightboxCaption = lightbox.querySelector('[data-lightbox-caption]');
            const closeButton = lightbox.querySelector('[data-lightbox-close]');
            const placeholderTemplate = document.getElementById('gallery-placeholder-template');
            const placeholderMarkup = placeholderTemplate ? placeholderTemplate.innerHTML.trim() : '<div class="gallery-placeholder"></div>';

            function showLoading() {
                state.classList.remove('hidden');
                grid.classList.add('hidden');
                grid.innerHTML = '';
                state.innerHTML = '<span class="spinner" aria-hidden="true"></span><p class="mt-4">Memuat galeri...</p>';
            }

            function showMessage(message, retry, danger) {
                state.classList.remove('hidden');
                grid.classList.add('hidden');
                grid.innerHTML = '';
                state.innerHTML = (danger
                    ? '<div class="alert alert--danger" role="alert">' + escapeHtml(message) + '</div>'
                    : '<p>' + escapeHtml(message) + '</p>')
                    + (retry ? '<button type="button" class="btn btn--link mt-4" data-gallery-retry>Coba lagi</button>' : '');
            }

            function closeLightbox() {
                lightbox.classList.remove('is-open');
                lightboxImage.removeAttribute('src');
                lightboxImage.classList.add('hidden');
                lightboxCaption.textContent = '';
                lightbox.setAttribute('aria-label', 'Foto kegiatan sekolah');
            }

            function openLightbox(item) {
                if (!item || !item.gambar_url) return;
                const description = item.deskripsi || 'Foto kegiatan sekolah';
                lightboxImage.src = item.gambar_url;
                lightboxImage.alt = description;
                lightboxCaption.textContent = item.deskripsi || '';
                lightbox.setAttribute('aria-label', description);
                lightboxImage.classList.remove('hidden');
                lightbox.classList.add('is-open');
                closeButton.focus();
            }

            function render(rows) {
                grid.innerHTML = rows.map((item, index) => {
                    const deskripsi = item && item.deskripsi ? String(item.deskripsi) : '';
                    const description = deskripsi || 'Foto kegiatan sekolah';
                    const gambarUrl = item && item.gambar_url ? String(item.gambar_url) : '';
                    const image = gambarUrl
                        ? '<img src="' + escapeHtml(gambarUrl) + '" alt="' + escapeHtml(description) + '" loading="lazy" />'
                        : placeholderMarkup;
                    const caption = deskripsi
                        ? '<figcaption class="gallery-card__caption"><p>' + escapeHtml(deskripsi) + '</p></figcaption>'
                        : '';
                    const attributes = gambarUrl
                        ? ' data-gallery-index="' + index + '" role="button" tabindex="0" aria-label="Lihat gambar penuh: ' + escapeHtml(description) + '"'
                        : '';

                    return '<figure class="gallery-card"' + attributes + '>' + image + caption + '</figure>';
                }).join('');

                state.classList.add('hidden');
                grid.classList.remove('hidden');

                grid.querySelectorAll('[data-gallery-index]').forEach((card) => {
                    const index = Number(card.getAttribute('data-gallery-index'));
                    card.addEventListener('click', () => openLightbox(rows[index]));
                    card.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            openLightbox(rows[index]);
                        }
                    });
                });
            }

            async function load() {
                showLoading();
                try {
                    const data = await api('/api/galeri');
                    const rows = Array.isArray(data) ? data : Array.isArray(data.data) ? data.data : [];
                    if (rows.length === 0) {
                        showMessage('Belum ada foto galeri.', false, false);
                        return;
                    }
                    render(rows);
                } catch (error) {
                    showMessage('Gagal memuat galeri. Silakan coba lagi.', true, true);
                }
            }

            state.addEventListener('click', (event) => {
                if (event.target.closest('[data-gallery-retry]')) void load();
            });
            closeButton.addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', (event) => {
                if (event.target === lightbox) closeLightbox();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && lightbox.classList.contains('is-open')) closeLightbox();
            });

            void load();
        })();
    </script>
@endpush
