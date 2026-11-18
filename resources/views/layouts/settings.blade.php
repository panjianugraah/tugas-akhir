@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
    <div class="content__header">
        <div>
            <h1 class="content__title">Pengaturan</h1>
            <p class="content__description">Kelola profil dan pengaturan akun Anda</p>
        </div>
    </div>

    <div class="settings-layout">
        <nav class="settings-nav" aria-label="Navigasi pengaturan">
            <a class="settings-nav__link {{ request()->routeIs('profile.edit') ? 'is-active' : '' }}" href="{{ route('profile.edit') }}">Profil</a>
            <a class="settings-nav__link {{ request()->routeIs('security.edit') ? 'is-active' : '' }}" href="{{ route('security.edit') }}">Keamanan</a>
            <a class="settings-nav__link {{ request()->routeIs('appearance.edit') ? 'is-active' : '' }}" href="{{ route('appearance.edit') }}">Tampilan</a>
        </nav>

        <div class="settings-content">
            @yield('settings-content')
        </div>
    </div>
@endsection
