@extends('layouts.admin')

@section('title', 'Laporan Sekolah')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Laporan Sekolah</h1>
            <p class="content__description">Pilih filter, kemudian unduh laporan dalam format PDF.</p>
        </div>
    </div>

    <div class="report-grid">
        <form class="card" method="GET" action="{{ route('admin.laporan.ppdb.pdf') }}" target="_blank" rel="noopener">
            <div class="card__header">
                <h2 class="card__title">Laporan PPDB</h2>
            </div>
            <div class="card__body">
                <p class="card__subtitle mb-4">Daftar pendaftar berdasarkan periode dan status.</p>

                <div class="form-field">
                    <label class="label" for="laporan-periode">Periode PPDB</label>
                    <select id="laporan-periode" class="select" name="periode_ppdb_id" required disabled>
                        <option value="">Memuat periode...</option>
                    </select>
                    <p id="error-periode" class="form-error hidden" role="alert">Gagal memuat pilihan periode.</p>
                </div>

                <div class="form-field mt-4">
                    <label class="label" for="laporan-status-ppdb">Status</label>
                    <select id="laporan-status-ppdb" class="select" name="status">
                        <option value="">Semua status</option>
                        <option value="menunggu">Menunggu</option>
                        <option value="diterima">Diterima</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <button id="submit-ppdb" type="submit" class="btn btn--outline mt-4" disabled>
                    <x-icon name="download" />
                    Generate PDF PPDB
                </button>
            </div>
        </form>

        <form class="card" method="GET" action="{{ route('admin.laporan.siswa.pdf') }}" target="_blank" rel="noopener">
            <div class="card__header">
                <h2 class="card__title">Laporan Siswa</h2>
            </div>
            <div class="card__body">
                <p class="card__subtitle mb-4">Daftar siswa berdasarkan kelas dan status.</p>

                <div class="form-field">
                    <label class="label" for="laporan-kelas">Kelas</label>
                    <select id="laporan-kelas" class="select" name="kelas_id" disabled>
                        <option value="">Semua kelas</option>
                    </select>
                    <p id="error-kelas" class="form-error hidden" role="alert">Gagal memuat pilihan kelas.</p>
                </div>

                <div class="form-field mt-4">
                    <label class="label" for="laporan-status-siswa">Status</label>
                    <select id="laporan-status-siswa" class="select" name="status">
                        <option value="">Semua status</option>
                        <option value="aktif">Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="pindah">Pindah</option>
                        <option value="keluar">Keluar</option>
                    </select>
                </div>

                <button id="submit-siswa" type="submit" class="btn btn--outline mt-4" disabled>
                    <x-icon name="download" />
                    Generate PDF Siswa
                </button>
            </div>
        </form>

        <form class="card" method="GET" action="{{ route('admin.laporan.guru.pdf') }}" target="_blank" rel="noopener">
            <div class="card__header">
                <h2 class="card__title">Laporan Guru</h2>
            </div>
            <div class="card__body">
                <p class="card__subtitle mb-4">Daftar guru berdasarkan status keaktifan.</p>

                <div class="form-field">
                    <label class="label" for="laporan-status-guru">Status</label>
                    <select id="laporan-status-guru" class="select" name="status">
                        <option value="">Semua status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <button type="submit" class="btn btn--outline mt-4">
                    <x-icon name="download" />
                    Generate PDF Guru
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const bersihkanSelect = (select) => {
                while (select && select.firstChild) {
                    select.removeChild(select.firstChild);
                }
            };

            const isiSelect = (select, items, placeholder, label) => {
                if (!select) return;

                bersihkanSelect(select);

                const awal = document.createElement('option');
                awal.value = '';
                awal.textContent = placeholder;
                select.appendChild(awal);

                items.forEach((item) => {
                    if (!item || item.id === undefined || item.id === null) return;

                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = label(item);
                    select.appendChild(option);
                });
            };

            const ambilData = async (url) => {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat data.');
                }

                const payload = await response.json();

                if (Array.isArray(payload)) return payload;
                if (payload && Array.isArray(payload.data)) return payload.data;

                return [];
            };

            const periodeSelect = document.getElementById('laporan-periode');
            const errorPeriode = document.getElementById('error-periode');
            const submitPpdb = document.getElementById('submit-ppdb');

            ambilData(@json(route('periode-ppdb.index')))
                .then((items) => {
                    isiSelect(periodeSelect, items, 'Pilih periode', (item) => item.nama_periode ?? '');
                    if (periodeSelect) periodeSelect.disabled = items.length === 0;
                    if (submitPpdb) submitPpdb.disabled = items.length === 0;
                })
                .catch(() => {
                    isiSelect(periodeSelect, [], 'Pilih periode', () => '');
                    if (periodeSelect) periodeSelect.disabled = false;
                    if (errorPeriode) errorPeriode.classList.remove('hidden');
                    if (submitPpdb) submitPpdb.disabled = true;
                });

            const kelasSelect = document.getElementById('laporan-kelas');
            const errorKelas = document.getElementById('error-kelas');
            const submitSiswa = document.getElementById('submit-siswa');

            ambilData(@json(route('kelas.index')))
                .then((items) => {
                    isiSelect(kelasSelect, items, 'Semua kelas', (item) => `${item.nama_kelas ?? ''} (${item.tahun_ajaran ?? ''})`);
                    if (kelasSelect) kelasSelect.disabled = false;
                    if (submitSiswa) submitSiswa.disabled = false;
                })
                .catch(() => {
                    isiSelect(kelasSelect, [], 'Semua kelas', () => '');
                    if (kelasSelect) kelasSelect.disabled = false;
                    if (errorKelas) errorKelas.classList.remove('hidden');
                    if (submitSiswa) submitSiswa.disabled = true;
                });
        })();
    </script>
@endpush
