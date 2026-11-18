@extends('layouts.settings')

@section('settings-content')
    @php
        $passwordRules = $passwordRules ?? '';
    @endphp

    <section class="card">
        <div class="card__header">
            <div>
                <h2 class="card__title">Perbarui sandi</h2>
                <p class="card__subtitle">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman</p>
            </div>
            <x-icon name="lock" />
        </div>

        <form method="POST" action="{{ route('user-password.update') }}">
            @csrf
            @method('PUT')

            <div class="card__body">
                <div class="stack gap-4">
                    <div class="form-field">
                        <label class="label" for="current_password">Kata sandi saat ini</label>
                        <input
                            class="input"
                            id="current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Kata sandi saat ini"
                            aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}"
                            required
                        />
                        @error('current_password')
                            <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label class="label" for="password">Sandi baru</label>
                            <input
                                class="input"
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Sandi baru"
                                passwordrules="{{ $passwordRules }}"
                                aria-describedby="{{ $passwordRules ? 'password-rules' : '' }}"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                required
                            />
                            @if ($passwordRules)
                                <p class="form-hint" id="password-rules">{{ $passwordRules }}</p>
                            @endif
                            @error('password')
                                <p class="form-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label class="label" for="password_confirmation">Konfirmasi sandi</label>
                            <input
                                class="input"
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Konfirmasi sandi"
                                passwordrules="{{ $passwordRules }}"
                                aria-describedby="{{ $passwordRules ? 'password-rules' : '' }}"
                                aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                                required
                            />
                            @error('password_confirmation')
                                <p class="form-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card__footer">
                <button type="submit" class="btn btn--brand" data-test="update-password-button">Simpan</button>
            </div>
        </form>
    </section>
@endsection
