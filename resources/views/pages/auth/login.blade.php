@extends('layouts.auth')

@section('auth-title', 'Log in to your account')
@section('auth-description', 'Enter your email and password below to log in')

@section('content')
    @if ($status ?? session('status'))
        <div class="alert alert--success mb-4" role="status">
            <x-icon name="check-circle" class="text-lg" />
            <span>{{ $status ?? session('status') }}</span>
        </div>
    @endif

    <form class="auth-card__form" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-field">
            <label class="label" for="email">Alamat email</label>
            <input
                class="input"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
                tabindex="1"
                @error('email') aria-invalid="true" @enderror
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <div class="auth-card__links">
                <label class="label" for="password">Kata sandi</label>
                @if ($canResetPassword ?? true)
                    <a class="btn btn--link btn--sm text-sm" href="{{ route('password.request') }}" tabindex="5">Lupa kata sandi?</a>
                @endif
            </div>
            <div class="row gap-2">
                <input
                    class="input"
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Kata sandi"
                    tabindex="2"
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

        <label class="checkbox-field" for="remember">
            <input
                class="checkbox"
                id="remember"
                name="remember"
                type="checkbox"
                value="1"
                tabindex="3"
                @if (old('remember')) checked @endif
            />
            <span>Ingat saya</span>
        </label>

        <button class="btn btn--brand btn--block btn--lg" type="submit" tabindex="4" data-test="login-button">Masuk</button>
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
