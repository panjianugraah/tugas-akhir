@extends('layouts.auth')

@section('auth-title', 'Forgot password')
@section('auth-description', 'Enter your email to receive a password reset link')

@section('content')
    @if ($status ?? session('status'))
        <div class="alert alert--success mb-4" role="status">
            <x-icon name="check-circle" class="text-lg" />
            <span>{{ $status ?? session('status') }}</span>
        </div>
    @endif

    <form class="auth-card__form" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-field">
            <label class="label" for="email">Alamat email</label>
            <input
                class="input"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="off"
                autofocus
                placeholder="email@example.com"
                @error('email') aria-invalid="true" @enderror
            />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="btn btn--brand btn--block btn--lg" type="submit" data-test="email-password-reset-link-button">Pengaturan ulang kata sandi melalui email tersebut</button>
    </form>

    <div class="auth-card__footer">
        <span>Atau, kembali ke</span> <a class="btn btn--link btn--sm" href="{{ route('login') }}">masuk</a>
    </div>
@endsection
