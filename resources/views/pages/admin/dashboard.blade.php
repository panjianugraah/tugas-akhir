@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    @php
        $formatAngka = static fn ($nilai) => number_format((int) $nilai, 0, ',', '.');
        $lebarBar = static fn ($jumlah, $maksimum) => $maksimum > 0
            ? round(((int) $jumlah / $maksimum) * 100, 2)
            : 0;

        $periodeItems = $periode ?? [];
        $grafikStatusItems = $grafikStatus ?? [];
        $grafikKelasItems = $grafikKelas ?? [];
        $pendaftarItems = collect($pendaftarTerbaru)->take(5);

        $namaPeriode = 'Belum ada periode';
        foreach ($periodeItems as $item) {
            if ((string) ($item['id'] ?? '') === (string) ($periodeTerpilih ?? '')) {
                $namaPeriode = (string) $item['nama_periode'];
                break;
            }
        }

        $maksimumStatus = 1;
        foreach ($grafikStatusItems as $item) {
            $jumlah = (int) ($item['jumlah'] ?? 0);
            if ($jumlah > $maksimumStatus) {
                $maksimumStatus = $jumlah;
            }
        }

        $maksimumKelas = 1;
        foreach ($grafikKelasItems as $item) {
            $jumlah = (int) ($item['jumlah'] ?? 0);
            if ($jumlah > $maksimumKelas) {
                $maksimumKelas = $jumlah;
            }
        }
    @endphp

    <div class="content__header">
        <div>
            <h1 class="content__title">Dashboard Sekolah</h1>
            <p class="content__description">Ringkasan data SMP Angkasa Adi Soemarmo.</p>
        </div>

        <a class="btn btn--brand" href="{{ route('admin.laporan') }}">
            <x-icon name="download" />
            Generate Laporan
        </a>
    </div>

    <form id="dashboard-periode-form" class="card" method="GET" action="{{ route('admin.dashboard') }}">
        <div class="card__body">
            <div class="form-field">
                <label class="label" for="dashboard-periode">Periode untuk ringkasan PPDB</label>
                <select
                    id="dashboard-periode"
                    class="select"
                    name="periode_ppdb_id"
                    @if (count($periodeItems) === 0) disabled @endif
                >
                    @if (count($periodeItems) === 0)
                        <option value="">Belum ada periode PPDB</option>
                    @endif

                    @foreach ($periodeItems as $item)
                        <option
                            value="{{ $item['id'] }}"
                            @if ((string) $item['id'] === (string) ($periodeTerpilih ?? '')) selected @endif
                        >{{ $item['nama_periode'] }}</option>
                    @endforeach
                </select>
            </div>

            <p class="form-hint mt-2">Filter ini hanya mengubah data PPDB, bukan data siswa, guru, atau kelas.</p>
            <p id="dashboard-periode-status" class="form-hint mt-2 hidden" role="status">Memuat periode...</p>
        </div>
    </form>

    <div class="grid grid--4 mt-6">
        <a class="card card--stat" href="{{ route('admin.siswa') }}">
            <div class="row row--between">
                <p class="stat__label">Siswa Aktif</p>
                <span class="badge badge--info"><x-icon name="graduation-cap" /></span>
            </div>
            <p class="stat__value">{{ $formatAngka($ringkasan['siswa_aktif'] ?? 0) }}</p>
            <p class="stat__note">Seluruh siswa berstatus aktif</p>
        </a>

        <a class="card card--stat" href="{{ route('admin.guru') }}">
            <div class="row row--between">
                <p class="stat__label">Guru Aktif</p>
                <span class="badge badge--success"><x-icon name="users" /></span>
            </div>
            <p class="stat__value">{{ $formatAngka($ringkasan['guru_aktif'] ?? 0) }}</p>
            <p class="stat__note">Seluruh guru berstatus aktif</p>
        </a>

        <a class="card card--stat" href="{{ route('admin.kelas') }}">
            <div class="row row--between">
                <p class="stat__label">Total Kelas</p>
                <span class="badge badge--neutral"><x-icon name="book" /></span>
            </div>
            <p class="stat__value">{{ $formatAngka($ringkasan['total_kelas'] ?? 0) }}</p>
            <p class="stat__note">Semua tahun ajaran yang tersimpan</p>
        </a>

        <a class="card card--stat" href="{{ route('admin.ppdb') }}">
            <div class="row row--between">
                <p class="stat__label">Pendaftar PPDB</p>
                <span class="badge badge--warning"><x-icon name="list" /></span>
            </div>
            <p class="stat__value">{{ $formatAngka($ringkasan['pendaftar_ppdb'] ?? 0) }}</p>
            <p class="stat__note">{{ $namaPeriode }}</p>
        </a>
    </div>

    <div class="chart-grid mt-6">
        <section class="card">
            <div class="card__header">
                <h2 class="text-lg font-semibold">Status Pendaftar PPDB</h2>
            </div>
            <div class="card__body">
                <p class="card__subtitle">{{ $namaPeriode }}</p>

                @if ((int) ($ringkasan['pendaftar_ppdb'] ?? 0) === 0)
                    <p class="empty-state">Belum ada pendaftar pada periode ini.</p>
                @else
                    <div class="mt-6">
                        @foreach ($grafikStatusItems as $item)
                            <div class="chart-row">
                                <span class="chart-row__label">{{ $item['label'] }}</span>
                                <div
                                    class="chart-row__track"
                                    role="img"
                                    aria-label="{{ $item['label'] }}: {{ (int) $item['jumlah'] }} pendaftar"
                                >
                                    <div
                                        class="chart-row__bar"
                                        style="width: {{ $lebarBar($item['jumlah'] ?? 0, $maksimumStatus) }}%"
                                    ></div>
                                </div>
                                <span class="chart-row__value nowrap">{{ $formatAngka($item['jumlah'] ?? 0) }} orang</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <p class="form-hint mt-4">Panjang batang menunjukkan jumlah orang, bukan persentase.</p>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2 class="text-lg font-semibold">Siswa Aktif per Kelas</h2>
            </div>
            <div class="card__body">
                <p class="card__subtitle">Maksimal 6 kelas dengan siswa aktif terbanyak.</p>

                @if (count($grafikKelasItems) === 0)
                    <p class="empty-state">Belum ada data kelas.</p>
                @else
                    <div class="mt-6">
                        @foreach ($grafikKelasItems as $item)
                            <div class="chart-row">
                                <span class="chart-row__label">{{ $item['label'] }}</span>
                                <div
                                    class="chart-row__track"
                                    role="img"
                                    aria-label="{{ $item['label'] }}: {{ (int) $item['jumlah'] }} siswa aktif"
                                >
                                    <div
                                        class="chart-row__bar"
                                        style="width: {{ $lebarBar($item['jumlah'] ?? 0, $maksimumKelas) }}%"
                                    ></div>
                                </div>
                                <span class="chart-row__value nowrap">{{ $formatAngka($item['jumlah'] ?? 0) }} siswa</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <p class="form-hint mt-4">Siswa aktif tanpa kelas: {{ $formatAngka($siswaTanpaKelas) }}. Grafik bukan keseluruhan jumlah siswa apabila terdapat lebih dari 6 kelas.</p>
            </div>
        </section>
    </div>

    <section class="card mt-6">
        <div class="card__header">
            <div>
                <h2 class="card__title">Pendaftar Terbaru</h2>
                <p class="card__subtitle">Maksimal 5 pendaftar — {{ $namaPeriode }}</p>
            </div>

            <a class="btn btn--link" href="{{ route('admin.ppdb') }}">Kelola PPDB →</a>
        </div>

        <div class="card__body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Asal Sekolah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($pendaftarItems->isEmpty())
                            <tr>
                                <td class="empty-state text-center" colspan="4">Belum ada pendaftar pada periode ini.</td>
                            </tr>
                        @else
                            @foreach ($pendaftarItems as $item)
                                @php
                                    $statusBadgeClass = [
                                        'menunggu' => 'badge--warning',
                                        'diterima' => 'badge--success',
                                        'ditolak' => 'badge--danger',
                                    ][$item['status']] ?? 'badge--neutral';
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="font-semibold">{{ $item['nama_lengkap'] }}</td>
                                    <td>{{ $item['asal_sekolah'] }}</td>
                                    <td><span class="badge {{ $statusBadgeClass }}">{{ $item['status'] }}</span></td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('dashboard-periode-form');
            const select = document.getElementById('dashboard-periode');
            const status = document.getElementById('dashboard-periode-status');

            if (!form || !select) return;

            select.addEventListener('change', function () {
                if (!select.value) return;

                if (status) status.classList.remove('hidden');
                form.submit();
            });
        })();
    </script>
@endpush
