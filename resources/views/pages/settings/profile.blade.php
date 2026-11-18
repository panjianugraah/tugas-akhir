@extends('layouts.settings')

@section('settings-content')
    @php
        $mustVerifyEmail = $mustVerifyEmail ?? false;
        $status = $status ?? null;
    @endphp

    <section class="card">
        <div class="card__header">
            <div>
                <h2 class="card__title">Profil</h2>
                <p class="card__subtitle">Perbarui nama dan alamat email Anda</p>
            </div>
            <x-icon name="user" />
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="card__body">
                @if ($status === 'verification-link-sent')
                    <div class="alert alert--success mb-4">
                        <x-icon name="check-circle" />
                        <span>Tautan verifikasi baru telah dikirim ke alamat email Anda.</span>
                    </div>
                @endif

                <div class="form-grid">
                    <div class="form-field">
                        <label class="label" for="name">Nama</label>
                        <input
                            class="input"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', auth()->user()->name) }}"
                            autocomplete="name"
                            placeholder="Nama lengkap"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                            required
                        />
                        @error('name')
                            <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label class="label" for="email">Alamat email</label>
                        <input
                            class="input"
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            autocomplete="username"
                            placeholder="Alamat email"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            required
                        />
                        @error('email')
                            <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($mustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div class="alert alert--warning mt-4">
                        <x-icon name="mail" />
                        <p>
                            Alamat email Anda belum diverifikasi.
                            <a class="btn btn--link btn--sm" href="{{ route('verification.notice') }}">Klik di sini untuk mengirim ulang email verifikasi.</a>
                        </p>
                    </div>
                @endif
            </div>

            <div class="card__footer">
                <button type="submit" class="btn btn--brand" data-test="update-profile-button">Simpan</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="card__header">
            <div>
                <h2 class="card__title">Hapus Akun</h2>
                <p class="card__subtitle">Hapus akun Anda beserta semua sumber dayanya</p>
            </div>
            <x-icon name="trash" />
        </div>

        <div class="card__body">
            <div class="alert alert--danger">
                <x-icon name="alert" />
                <div>
                    <p class="font-semibold">Peringatan</p>
                    <p class="text-sm mt-1">Harap berhati-hati, tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn--danger" id="delete-user-button" data-test="delete-user-button">Hapus Akun</button>
            </div>
        </div>
    </section>

    <div
        class="modal {{ $errors->has('password') ? 'is-open' : '' }}"
        id="delete-user-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-user-modal-title"
        aria-hidden="{{ $errors->has('password') ? 'false' : 'true' }}"
    >
        <div class="modal__dialog">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal__header">
                    <h3 class="modal__title" id="delete-user-modal-title">Apakah Anda yakin ingin menghapus akun Anda?</h3>
                    <button type="button" class="btn btn--ghost btn--icon btn--sm" data-close-delete-user aria-label="Tutup">
                        <x-icon name="x" />
                    </button>
                </div>

                <div class="modal__body">
                    <p class="text-sm muted">
                        Setelah akun Anda dihapus, semua sumber daya dan data di dalamnya juga akan dihapus secara permanen. Harap masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.
                    </p>

                    <div class="form-field mt-4">
                        <label class="label" for="delete-password">Kata Sandi</label>
                        <input
                            class="input"
                            id="delete-password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Kata sandi"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            required
                        />
                        @error('password')
                            <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="modal__footer">
                    <button type="button" class="btn btn--secondary" data-close-delete-user>Batal</button>
                    <button type="submit" class="btn btn--danger" data-test="confirm-delete-user-button">Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('delete-user-modal');
            const openButton = document.getElementById('delete-user-button');
            const passwordInput = document.getElementById('delete-password');

            if (!modal) return;

            const closeButtons = modal.querySelectorAll('[data-close-delete-user]');

            const openModal = function () {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                if (passwordInput) passwordInput.focus();
            };

            const closeModal = function () {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            };

            if (openButton) openButton.addEventListener('click', openModal);

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            if (modal.classList.contains('is-open') && passwordInput) {
                passwordInput.focus();
            }
        })();
    </script>
@endpush
