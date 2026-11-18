@extends('layouts.admin')

@section('title', 'Data Pendaftar PPDB')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Data Pendaftar PPDB</h1>
            <p class="content__description">Verifikasi calon peserta didik baru</p>
        </div>
    </div>

    <div class="filter-bar">
        <input type="search" id="ppdb-search" class="input search-input" placeholder="Cari..." aria-label="Cari pendaftar" />
        <select id="filter-status-ppdb" class="select" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="menunggu">Menunggu</option>
            <option value="diterima">Diterima</option>
            <option value="ditolak">Ditolak</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Asal Sekolah</th>
                    <th>No. HP</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="ppdb-rows">
                <tr><td colspan="7" class="empty-state">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
    <p id="ppdb-count" class="form-hint mt-2">Menampilkan 0 dari 0 data</p>
@endsection

@push('scripts')
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            async function api(url, options = {}) {
                const res = await fetch(url, {
                    ...options,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        ...(options.headers || {}),
                    },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw Object.assign(new Error(data.message || 'Gagal.'), { status: res.status, data });
                return data;
            }

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[char]);

            const rowsEl = document.getElementById('ppdb-rows');
            const countEl = document.getElementById('ppdb-count');
            const searchEl = document.getElementById('ppdb-search');
            const statusEl = document.getElementById('filter-status-ppdb');

            let rows = [];

            const badgeClass = (status) => {
                if (status === 'diterima') return 'badge--success';
                if (status === 'menunggu') return 'badge--warning';
                if (status === 'ditolak') return 'badge--danger';
                return 'badge--neutral';
            };

            const render = () => {
                const query = searchEl.value.trim().toLowerCase();
                const filtered = rows.filter((row) => [
                    row.nisn,
                    row.nama_lengkap,
                    row.asal_sekolah,
                    row.no_hp,
                    row.status,
                ].some((value) => String(value ?? '').toLowerCase().includes(query)));

                if (!filtered.length) {
                    rowsEl.innerHTML = '<tr><td colspan="7" class="empty-state">Tidak ada data.</td></tr>';
                } else {
                    rowsEl.innerHTML = filtered.map((row, index) => {
                        const id = escapeHtml(row.id);
                        const status = escapeHtml(row.status);
                        return '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + escapeHtml(row.nisn) + '</td>' +
                            '<td>' + escapeHtml(row.nama_lengkap) + '</td>' +
                            '<td>' + escapeHtml(row.asal_sekolah) + '</td>' +
                            '<td>' + escapeHtml(row.no_hp) + '</td>' +
                            '<td><span class="badge ' + badgeClass(row.status) + '" style="text-transform: capitalize">' + status + '</span></td>' +
                            '<td><div class="table__actions">' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="diterima" data-id="' + id + '" title="Terima" style="color: var(--green-700)">Terima</button>' +
                                '<button type="button" class="btn btn--sm btn--outline" data-action="ditolak" data-id="' + id + '" title="Tolak" style="color: var(--amber-700)">Tolak</button>' +
                                '<button type="button" class="btn btn--sm btn--danger" data-action="hapus" data-id="' + id + '" title="Hapus">Hapus</button>' +
                            '</div></td>' +
                        '</tr>';
                    }).join('');
                }

                countEl.textContent = 'Menampilkan ' + filtered.length + ' dari ' + rows.length + ' data';
            };

            const load = async () => {
                const query = statusEl.value ? '/api/ppdb?status=' + encodeURIComponent(statusEl.value) : '/api/ppdb';
                rowsEl.innerHTML = '<tr><td colspan="7" class="empty-state">Memuat data...</td></tr>';
                try {
                    const response = await api(query);
                    const payload = response?.data?.data ?? response?.data ?? response;
                    rows = Array.isArray(payload) ? payload : [];
                } catch (error) {
                    rows = [];
                }
                render();
            };

            const findRow = (id) => rows.find((row) => String(row.id) === String(id));

            const ubahStatus = async (id, status) => {
                try {
                    await api('/api/ppdb/' + id, {
                        method: 'PUT',
                        body: JSON.stringify({ status: status }),
                        headers: { 'Content-Type': 'application/json' },
                    });
                    await load();
                } catch (error) {
                    alert(error.data?.message || 'Gagal mengubah status.');
                }
            };

            const hapus = async (id) => {
                if (!confirm('Hapus data pendaftar ini?')) return;
                try {
                    await api('/api/ppdb/' + id, { method: 'DELETE' });
                    await load();
                } catch (error) {
                    alert(error.data?.message || 'Gagal menghapus data pendaftar.');
                }
            };

            rowsEl.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) return;
                const row = findRow(button.dataset.id);
                if (!row) return;
                const action = button.dataset.action;
                if (action === 'diterima' || action === 'ditolak') ubahStatus(row.id, action);
                if (action === 'hapus') hapus(row.id);
            });

            searchEl.addEventListener('input', render);
            statusEl.addEventListener('change', load);

            load();
        })();
    </script>
@endpush
