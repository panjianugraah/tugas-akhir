@extends('layouts.admin')

@section('title', 'Tambah Akun Pengguna')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Tambah Akun Pengguna</h1>
            <p class="content__description">Buat akun baru tanpa keluar dari akun admin.</p>
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <h2 class="card__title">Data Akun</h2>
        </div>
        <div class="card__body">
            <form id="tambah-pengguna-form" method="POST" action="{{ route('admin.pengguna.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="form-field">
                        <label class="label" for="name">Nama</label>
                        <input
                            id="name"
                            class="input"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="off"
                            @error('name') aria-invalid="true" @enderror
                        />
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label class="label" for="email">Email</label>
                        <input
                            id="email"
                            class="input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="off"
                            @error('email') aria-invalid="true" @enderror
                        />
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label class="label" for="password">Password</label>
                        <input
                            id="password"
                            class="input"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            @error('password') aria-invalid="true" @enderror
                        />
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label class="label" for="password_confirmation">Konfirmasi Password</label>
                        <input
                            id="password_confirmation"
                            class="input"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            @error('password_confirmation') aria-invalid="true" @enderror
                        />
                        @error('password_confirmation')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button id="tambah-pengguna-submit" type="submit" class="btn">
                        Buat Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('tambah-pengguna-form');
            const button = document.getElementById('tambah-pengguna-submit');

            if (!form || !button) return;

            form.addEventListener('submit', function () {
                button.disabled = true;
                button.textContent = 'Menyimpan...';
            });
        })();
    </script>
@endpush
