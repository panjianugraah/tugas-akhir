@extends('layouts.public')

@section('title', 'Pengumuman')

@section('content')
    <section class="section">
        <div class="container container--narrow">
            <a class="btn btn--link btn--sm" href="{{ route('pengumuman.publik.index') }}">← Semua Pengumuman</a>

            <p id="detail-loading" class="empty-state" role="status">Memuat...</p>

            <div id="detail-error" class="alert alert--danger mt-8 hidden" role="alert">
                <span id="detail-error-message"></span>
            </div>

            <article class="card mt-6 hidden" id="detail-article">
                <div class="card__body">
                    <h1 class="section-title" id="detail-title"></h1>
                    <p class="text-sm muted mt-2 hidden" id="detail-date"></p>
                    <img class="mt-6 hidden" id="detail-image" alt="">
                    <div class="mt-6" id="detail-body"></div>
                </div>
            </article>
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

        const slug = @json($slug);
        const loading = document.getElementById('detail-loading');
        const errorBox = document.getElementById('detail-error');
        const errorMessage = document.getElementById('detail-error-message');
        const article = document.getElementById('detail-article');
        const title = document.getElementById('detail-title');
        const date = document.getElementById('detail-date');
        const image = document.getElementById('detail-image');
        const body = document.getElementById('detail-body');

        body.style.whiteSpace = 'pre-wrap';
        body.style.overflowWrap = 'anywhere';
        body.style.lineHeight = '1.7';

        async function loadDetail() {
            loading.classList.remove('hidden');
            errorBox.classList.add('hidden');
            article.classList.add('hidden');

            try {
                const response = await api('/api/pengumuman/slug/' + encodeURIComponent(slug));
                const item = response && response.data ? response.data : response;

                title.textContent = item.judul || '';
                document.title = item.judul || 'Pengumuman';

                if (item.berlaku_sampai) {
                    const tanggal = new Intl.DateTimeFormat('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    }).format(new Date(item.berlaku_sampai + 'T00:00:00'));

                    date.textContent = 'Berlaku sampai: ' + tanggal;
                    date.classList.remove('hidden');
                } else {
                    date.classList.add('hidden');
                }

                if (item.gambar_url) {
                    image.src = item.gambar_url;
                    image.alt = item.judul || '';
                    image.classList.remove('hidden');
                } else {
                    image.classList.add('hidden');
                    image.removeAttribute('src');
                }

                body.textContent = item.isi || '';
                article.classList.remove('hidden');
            } catch (error) {
                errorMessage.textContent = error.status === 404
                    ? 'Pengumuman tidak ditemukan atau sudah tidak tersedia.'
                    : 'Gagal memuat pengumuman.';
                errorBox.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }

        void loadDetail();
    </script>
@endpush
