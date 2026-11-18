@extends('layouts.auth')

@section('auth-title', 'Create an account')
@section('auth-description', 'Enter your details below to create your account')

@section('content')
    <form class="auth-card__form" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-field">
            <label class="label" for="name">Name</label>
            <input
                class="input"
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Full name"
                tabindex="1"
                @error('name') aria-invalid="true" @enderror
            />
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="label" for="email">Email address</label>
            <input
                class="input"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                placeholder="email@example.com"
                tabindex="2"
                @error('email') aria-invalid="true" @enderror
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="label" for="password">Password</label>
            <div class="row gap-2">
                <input
                    class="input"
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Password"
                    passwordrules="{{ $passwordRules ?? '' }}"
                    tabindex="3"
                    @error('password') aria-invalid="true" @enderror
                />
                <button class="btn btn--ghost btn--icon" type="button" data-password-toggle aria-label="Show password" tabindex="-1">
                    <x-icon name="eye" class="text-sm" data-password-icon="show" />
                    <x-icon name="eye-off" class="text-sm hidden" data-password-icon="hide" />
                </button>
            </div>
            @if ($passwordRules ?? false)
                <p class="form-hint">{{ $passwordRules }}</p>
            @endif
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label class="label" for="password_confirmation">Confirm password</label>
            <div class="row gap-2">
                <input
                    class="input"
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm password"
                    passwordrules="{{ $passwordRules ?? '' }}"
                    tabindex="4"
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

        <button class="btn btn--block btn--lg" type="submit" tabindex="5" data-test="register-user-button">Create account</button>

        <div class="auth-card__footer">
            Already have an account? <a class="btn btn--link btn--sm" href="{{ route('login') }}" tabindex="6">Log in</a>
        </div>
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
