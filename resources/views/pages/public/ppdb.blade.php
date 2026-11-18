@extends('layouts.public')

@section('title', 'PPDB')

@section('content')
    <section class="page-header">
        <div class="page-header__inner">
            <p class="page-header__eyebrow" id="ppdb-eyebrow">Penerimaan Peserta Didik Baru</p>
            <h1 class="page-header__title">Penerimaan Peserta Didik Baru</h1>
            <p class="page-header__description has-button">
                Bergabunglah bersama SMP Angkasa Adi Soemarmo, sekolah berakreditasi A
                di bawah naungan TNI AU dan Yayasan Ardhya Garini.
            </p>
            <a class="btn btn--yellow btn--lg" href="{{ route('formppdb') }}">
                Daftar Sekarang →
            </a>
        </div>
    </section>

    <section class="section">
        <div class="container container--narrow">
            <h2 class="section-title text-center">Jadwal PPDB</h2>

            <div id="jadwal-loading" class="empty-state" role="status">Memuat jadwal PPDB...</div>

            <div id="jadwal-error" class="text-center hidden">
                <p class="form-error" role="alert" id="jadwal-error-message"></p>
                <button type="button" class="btn btn--link mt-2" id="jadwal-retry">Coba lagi</button>
            </div>

            <p id="jadwal-empty" class="empty-state hidden">
                Belum ada periode PPDB yang dibuka atau dijadwalkan.
            </p>

            <div id="jadwal-list" class="grid grid--2 hidden"></div>
        </div>
    </section>

    <section class="section section--soft">
        <div class="container container--narrow">
            <h2 class="section-title text-center">Persyaratan</h2>
            <ul class="grid grid--2 gap-4">
                <li class="feature-item card__body">
                    <span class="feature-icon"><x-icon name="check" /></span>
                    <span class="feature-item__text">Fotokopi ijazah / Surat Keterangan Lulus SD/MI</span>
                </li>
                <li class="feature-item card__body">
                    <span class="feature-icon"><x-icon name="check" /></span>
                    <span class="feature-item__text">Fotokopi akta kelahiran</span>
                </li>
                <li class="feature-item card__body">
                    <span class="feature-icon"><x-icon name="check" /></span>
                    <span class="feature-item__text">Fotokopi Kartu Keluarga (KK)</span>
                </li>
                <li class="feature-item card__body">
                    <span class="feature-icon"><x-icon name="check" /></span>
                    <span class="feature-item__text">Pas foto 3x4 berwarna (3 lembar)</span>
                </li>
                <li class="feature-item card__body">
                    <span class="feature-icon"><x-icon name="check" /></span>
                    <span class="feature-item__text">Fotokopi NISN</span>
                </li>
                <li class="feature-item card__body">
                    <span class="feature-icon"><x-icon name="check" /></span>
                    <span class="feature-item__text">Fotokopi KIP/PKH (jika ada)</span>
                </li>
            </ul>
        </div>
    </section>

    <section class="section">
        <div class="container container--narrow">
            <h2 class="section-title text-center">Alur Pendaftaran</h2>
            <div class="grid grid--4 gap-6">
                <div class="text-center">
                    <div class="row row--center"><div class="mission-number mb-2">1</div></div>
                    <p class="font-semibold mb-2">Isi Formulir Online</p>
                    <p class="text-sm muted">Lengkapi data calon peserta didik melalui form pendaftaran.</p>
                </div>
                <div class="text-center">
                    <div class="row row--center"><div class="mission-number mb-2">2</div></div>
                    <p class="font-semibold mb-2">Verifikasi Berkas</p>
                    <p class="text-sm muted">Bawa berkas persyaratan ke sekolah untuk diverifikasi panitia.</p>
                </div>
                <div class="text-center">
                    <div class="row row--center"><div class="mission-number mb-2">3</div></div>
                    <p class="font-semibold mb-2">Tes &amp; Wawancara</p>
                    <p class="text-sm muted">Ikuti tes dan wawancara sesuai jadwal yang ditentukan.</p>
                </div>
                <div class="text-center">
                    <div class="row row--center"><div class="mission-number mb-2">4</div></div>
                    <p class="font-semibold mb-2">Pengumuman &amp; Daftar Ulang</p>
                    <p class="text-sm muted">Cek hasil pengumuman dan lakukan daftar ulang.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="page-header">
        <div class="page-header__inner">
            <h2 class="page-header__title">Siap bergabung?</h2>
            <p class="page-header__description has-button">Isi formulir pendaftaran online sekarang juga.</p>
            <a class="btn btn--yellow btn--lg" href="{{ route('formppdb') }}">
                Isi Formulir Pendaftaran
            </a>
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

        const jadwalLoading = document.getElementById('jadwal-loading');
        const jadwalError = document.getElementById('jadwal-error');
        const jadwalErrorMessage = document.getElementById('jadwal-error-message');
        const jadwalEmpty = document.getElementById('jadwal-empty');
        const jadwalList = document.getElementById('jadwal-list');
        const ppdbEyebrow = document.getElementById('ppdb-eyebrow');

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        function formatTanggal(tanggal) {
            return new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'long',
                timeZone: 'UTC',
            }).format(new Date(tanggal));
        }

        async function loadPeriode() {
            jadwalLoading.classList.remove('hidden');
            jadwalError.classList.add('hidden');
            jadwalEmpty.classList.add('hidden');
            jadwalList.classList.add('hidden');

            try {
                const response = await api('/api/periode-ppdb/jadwal');
                const periode = Array.isArray(response?.data) ? response.data : [];

                if (periode.length > 0 && periode[0].nama_periode) {
                    ppdbEyebrow.textContent = periode[0].nama_periode;
                }

                if (periode.length === 0) {
                    jadwalEmpty.classList.remove('hidden');
                    return;
                }

                jadwalList.innerHTML = periode.map((item) => {
                    const kuota = item.kuota !== null && item.kuota !== undefined
                        ? '<p class="mt-2 text-sm muted">Kuota: ' + escapeHtml(item.kuota) + ' peserta</p>'
                        : '';

                    return '<div class="card">' +
                        '<div class="card__body">' +
                            '<p class="font-semibold">' + escapeHtml(item.nama_periode) + '</p>' +
                            '<p class="mt-1 text-sm muted">' + escapeHtml(formatTanggal(item.tanggal_mulai)) + ' – ' + escapeHtml(formatTanggal(item.tanggal_selesai)) + '</p>' +
                            kuota +
                        '</div>' +
                    '</div>';
                }).join('');
                jadwalList.classList.remove('hidden');
            } catch (error) {
                jadwalErrorMessage.textContent = 'Jadwal PPDB gagal dimuat. Silakan coba lagi.';
                jadwalError.classList.remove('hidden');
            } finally {
                jadwalLoading.classList.add('hidden');
            }
        }

        document.getElementById('jadwal-retry').addEventListener('click', () => {
            void loadPeriode();
        });

        void loadPeriode();
    </script>
@endpush
