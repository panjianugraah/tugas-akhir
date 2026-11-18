@extends('layouts.auth')

@section('auth-title', 'Email verification')
@section('auth-description', 'Please verify your email address by clicking on the link we just emailed to you.')

@section('content')
    @if (($status ?? session('status')) === 'verification-link-sent')
        <div class="alert alert--success mb-4" role="status">
            <x-icon name="check-circle" class="text-lg" />
            <span>Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.</span>
        </div>
    @endif

    <form class="auth-card__form text-center" method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn--secondary btn--block" type="submit">Kirim ulang email verifikasi</button>
    </form>

    <div class="auth-card__footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn--link btn--sm" type="submit">Keluar</button>
        </form>
    </div>
@endsection
