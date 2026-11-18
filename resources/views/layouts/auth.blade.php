@extends('layouts.base')

@section('body')
    <div class="auth-shell">
        <div class="auth-card card" style="padding: 2rem">
            <div class="auth-card__brand">
                <img
                    src="{{ asset('storage/smp.jpg') }}"
                    onerror="this.onerror=null;this.src='{{ asset('apple-touch-icon.png') }}';"
                    alt="Logo"
                />
            </div>
            <h1 class="auth-card__title">@yield('auth-title')</h1>
            <p class="auth-card__description">@yield('auth-description')</p>

            @yield('content')
        </div>
    </div>
@endsection
