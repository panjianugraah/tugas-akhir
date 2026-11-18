@extends('layouts.auth')

@section('auth-title', 'Confirm password')
@section('auth-description', 'This is a secure area of the application. Please confirm your password before continuing.')

@section('content')
    <div class="alert alert--info mb-4">
        <x-icon name="key" class="text-lg" />
        <span>Konfirmasi dengan passkey tersedia melalui passkey browser jika diaktifkan. Atau konfirmasi dengan kata sandi.</span>
    </div>

    <form class="auth-card__form" method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="form-field">
            <label class="label" for="password">Kata sandi</label>
            <div class="row gap-2">
                <input
                    class="input"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    autofocus
                    placeholder="Kata sandi"
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

        <button class="btn btn--brand btn--block btn--lg" type="submit" data-test="confirm-password-button">Konfirmasi kata sandi</button>
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
