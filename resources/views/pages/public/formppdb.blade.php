@extends('layouts.public')

@section('title', 'Formulir PPDB')

@section('content')
    <section class="section">
        <div class="container container--narrow">
            <a class="btn btn--link btn--sm" href="{{ route('ppdb') }}">← Kembali ke info PPDB</a>

            <div class="card mt-4">
                <div class="card__body">
                    <h1 class="section-title mb-2">Formulir Pendaftaran</h1>
                    <p class="muted mb-8">Calon Peserta Didik Baru SMP Angkasa Adi Soemarmo</p>

                    <div class="alert alert--success mb-6 hidden" id="ppdb-success" role="status">
                        <span>✅ Pendaftaran berhasil dikirim! Silakan bawa berkas persyaratan ke sekolah untuk verifikasi.</span>
                    </div>

                    <div class="alert alert--danger mb-6 hidden" id="ppdb-error" role="alert">
                        <span id="ppdb-error-message"></span>
                    </div>

                    <form id="form-ppdb" novalidate>
                        <h2 class="card__title mb-4">Periode Pendaftaran</h2>

                        <div class="form-field mb-4" data-field="periode_ppdb_id">
                            <label class="label" for="periode_ppdb_id">Pilih Periode PPDB</label>
                            <select class="select" id="periode_ppdb_id" name="periode_ppdb_id" aria-label="Periode PPDB" required disabled>
                                <option value="">Memuat periode...</option>
                            </select>
                            <p class="form-error hidden"></p>
                        </div>

                        <p class="form-error hidden" id="error-periode" role="alert"></p>

                        <div class="alert alert--warning mb-4 hidden" id="periode-kosong">
                            Saat ini belum ada periode pendaftaran yang dibuka.
                        </div>

                        <h2 class="card__title mt-8 mb-4">Data Calon Siswa</h2>

                        <div class="form-field mb-4" data-field="nama_lengkap">
                            <label class="label" for="nama_lengkap">Nama Lengkap</label>
                            <input class="input" id="nama_lengkap" name="nama_lengkap" type="text" maxlength="255" required>
                            <p class="form-error hidden"></p>
                        </div>

                        <div class="form-grid mb-4">
                            <div class="form-field" data-field="nisn">
                                <label class="label" for="nisn">NISN</label>
                                <input class="input" id="nisn" name="nisn" type="text" inputmode="numeric" maxlength="10" required>
                                <p class="form-error hidden"></p>
                            </div>

                            <div class="form-field" data-field="jenis_kelamin">
                                <label class="label" for="jenis_kelamin">Jenis Kelamin</label>
                                <select class="select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <p class="form-error hidden"></p>
                            </div>

                            <div class="form-field" data-field="tempat_lahir">
                                <label class="label" for="tempat_lahir">Tempat Lahir</label>
                                <input class="input" id="tempat_lahir" name="tempat_lahir" type="text" maxlength="100" required>
                                <p class="form-error hidden"></p>
                            </div>

                            <div class="form-field" data-field="tanggal_lahir">
                                <label class="label" for="tanggal_lahir">Tanggal Lahir</label>
                                <input class="input" id="tanggal_lahir" name="tanggal_lahir" type="date" required>
                                <p class="form-error hidden"></p>
                            </div>
                        </div>

                        <div class="form-field mb-4" data-field="asal_sekolah">
                            <label class="label" for="asal_sekolah">Asal Sekolah (SD/MI)</label>
                            <input class="input" id="asal_sekolah" name="asal_sekolah" type="text" maxlength="255" required>
                            <p class="form-error hidden"></p>
                        </div>

                        <h2 class="card__title mt-8 mb-4">Data Orang Tua / Wali</h2>

                        <div class="form-field mb-4" data-field="nama_orang_tua">
                            <label class="label" for="nama_orang_tua">Nama Orang Tua / Wali</label>
                            <input class="input" id="nama_orang_tua" name="nama_orang_tua" type="text" maxlength="255" required>
                            <p class="form-error hidden"></p>
                        </div>

                        <div class="form-grid mb-4">
                            <div class="form-field" data-field="no_hp">
                                <label class="label" for="no_hp">No. HP / WhatsApp</label>
                                <input class="input" id="no_hp" name="no_hp" type="tel" maxlength="20" required>
                                <p class="form-error hidden"></p>
                            </div>

                            <div class="form-field" data-field="email">
                                <label class="label" for="email">Email (opsional)</label>
                                <input class="input" id="email" name="email" type="email" maxlength="255">
                                <p class="form-error hidden"></p>
                            </div>
                        </div>

                        <div class="form-field mb-4" data-field="alamat">
                            <label class="label" for="alamat">Alamat Lengkap</label>
                            <textarea class="textarea" id="alamat" name="alamat" rows="3" required></textarea>
                            <p class="form-error hidden"></p>
                        </div>

                        <div class="form-actions">
                            <button class="btn btn--brand btn--block" id="submit-ppdb" type="submit" disabled>
                                Kirim Pendaftaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

        const form = document.getElementById('form-ppdb');
        const periodeSelect = document.getElementById('periode_ppdb_id');
        const errorPeriode = document.getElementById('error-periode');
        const periodeKosong = document.getElementById('periode-kosong');
        const successBanner = document.getElementById('ppdb-success');
        const errorBanner = document.getElementById('ppdb-error');
        const errorMessage = document.getElementById('ppdb-error-message');
        const submitButton = document.getElementById('submit-ppdb');
        const nisnInput = document.getElementById('nisn');

        let periode = [];
        let loadingPeriode = true;
        let errorPeriodeAktif = '';
        let processing = false;

        function setFieldError(name, messages) {
            const field = form.querySelector('[data-field="' + name + '"]');
            if (!field) return;

            const pesan = Array.isArray(messages) ? messages[0] : messages;
            const paragraph = field.querySelector('.form-error');
            const control = field.querySelector('input, select, textarea');

            paragraph.textContent = pesan || '';
            paragraph.classList.toggle('hidden', !pesan);

            if (control) {
                if (pesan) control.setAttribute('aria-invalid', 'true');
                else control.removeAttribute('aria-invalid');
            }
        }

        function clearErrors() {
            form.querySelectorAll('[data-field]').forEach((field) => {
                setFieldError(field.dataset.field, '');
            });
            errorBanner.classList.add('hidden');
            errorMessage.textContent = '';
        }

        function updateState() {
            periodeSelect.disabled = processing || loadingPeriode || errorPeriodeAktif !== '' || periode.length === 0;
            submitButton.disabled = processing || loadingPeriode || errorPeriodeAktif !== '' || periode.length === 0 || !periodeSelect.value;
            submitButton.textContent = processing ? 'Mengirim...' : 'Kirim Pendaftaran';
        }

        async function loadPeriode() {
            loadingPeriode = true;
            errorPeriodeAktif = '';
            errorPeriode.classList.add('hidden');
            periodeKosong.classList.add('hidden');
            updateState();

            try {
                const response = await api('/api/periode-ppdb/aktif');
                periode = Array.isArray(response?.data) ? response.data : [];

                const pilihan = periodeSelect.querySelector('option');
                pilihan.textContent = '-- Pilih periode --';

                periode.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.nama_periode;
                    periodeSelect.appendChild(option);
                });

                if (periode.length === 1) {
                    periodeSelect.value = String(periode[0].id);
                }

                if (periode.length === 0) {
                    periodeKosong.classList.remove('hidden');
                }
            } catch (error) {
                errorPeriodeAktif = 'Gagal memuat periode pendaftaran.';
                errorPeriode.textContent = errorPeriodeAktif;
                errorPeriode.classList.remove('hidden');
            } finally {
                loadingPeriode = false;
                updateState();
            }
        }

        nisnInput.addEventListener('input', () => {
            nisnInput.value = nisnInput.value.replace(/\D/g, '');
        });

        form.querySelectorAll('input, select, textarea').forEach((control) => {
            control.addEventListener('input', () => {
                setFieldError(control.name, '');
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (processing) return;

            clearErrors();
            successBanner.classList.add('hidden');

            if (!periodeSelect.value) {
                setFieldError('periode_ppdb_id', ['Pilih periode pendaftaran terlebih dahulu.']);
                return;
            }

            if (loadingPeriode || errorPeriodeAktif || periode.length === 0) {
                return;
            }

            processing = true;
            updateState();

            const payload = {
                periode_ppdb_id: Number(periodeSelect.value),
                nama_lengkap: form.elements.nama_lengkap.value,
                nisn: form.elements.nisn.value,
                jenis_kelamin: form.elements.jenis_kelamin.value,
                tempat_lahir: form.elements.tempat_lahir.value,
                tanggal_lahir: form.elements.tanggal_lahir.value,
                asal_sekolah: form.elements.asal_sekolah.value,
                nama_orang_tua: form.elements.nama_orang_tua.value,
                no_hp: form.elements.no_hp.value,
                email: form.elements.email.value,
                alamat: form.elements.alamat.value,
            };

            try {
                await api('/api/ppdb', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });

                successBanner.classList.remove('hidden');
                form.reset();
                periodeSelect.value = String(payload.periode_ppdb_id);
            } catch (error) {
                if (error.status === 422 && error.data && error.data.errors) {
                    Object.entries(error.data.errors).forEach(([field, messages]) => {
                        setFieldError(field, messages);
                    });
                } else {
                    errorMessage.textContent = 'Gagal mengirim pendaftaran.';
                    errorBanner.classList.remove('hidden');
                }
            } finally {
                processing = false;
                updateState();
            }
        });

        void loadPeriode();
    </script>
@endpush
