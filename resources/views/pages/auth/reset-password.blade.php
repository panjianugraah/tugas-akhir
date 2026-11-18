@extends('layouts.auth')

@section('auth-title', 'Reset password')
@section('auth-description', 'Please enter your new password below')

@section('content')
    <form class="auth-card__form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}" />
        <input type="hidden" name="email" value="{{ old('email', $email ?? request('email', '')) }}" />

        <div class="form-field">
            <label class="label" for="email">Email</label>
            <input
                class="input"
                id="email"
                type="email"
                value="{{ old('email', $email ?? request('email', '')) }}"
                autocomplete="email"
                readonly
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="label" for="password">Kata sandi</label>
            <div class="row gap-2">
                <input
                    class="input"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    autofocus
                    placeholder="Password"
                    passwordrules="{{ $passwordRules ?? '' }}"
                    @error('password') aria-invalid="true" @enderror
                />
                <button class="btn btn--ghost btn--icon" type="button" data-password-toggle aria-label="Show password" tabindex="-1">
                    <x-icon name="eye" class="text-sm" data-password-icon="show" />
                    <x-icon name="eye-off" class="text-sm hidden" data-password-icon="hide" />
                </button>
            </div>
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="label" for="password_confirmation">Konfirmasi kata sandi</label>
            <div class="row gap-2">
                <input
                    class="input"
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Confirm password"
                    passwordrules="{{ $passwordRules ?? '' }}"
                    @error('password_confirmation') aria-invalid="true" @enderror
                />
                <button class="btn btn--ghost btn--icon" type="button" data-password-toggle aria-label="Show password" tabindex="-1">
                    <x-icon name="eye" class="text-sm" data-password-icon="show" />
                    <x-icon name="eye-off" class="text-sm hidden" data-password-icon="hide" />
                </button>
            </div>
            @error('password_confirmation')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="btn btn--block btn--lg" type="submit" data-test="reset-password-button">Reset kata sandi</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = button.parentElement.querySelector('input');

                if (!input) {
                    return;
                }

                var show = input.type === 'password';
                var eye = button.querySelector('[data-password-icon="show"]');
                var eyeOff = button.querySelector('[data-password-icon="hide"]');

                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

                if (eye && eyeOff) {
                    eye.classList.toggle('hidden', !show);
                    eyeOff.classList.toggle('hidden', show);
                }

                input.focus();
            });
        });
    </script>
@endpush
