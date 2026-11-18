@extends('layouts.base')

@section('body')
    @php
        $user = Auth::user();
        $isAdmin = $user && $user->can('access-admin');
        $canManageUsers = $user && $user->can('manage-users');
    @endphp

    <div class="admin-shell">
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar__brand">
                <img
                    src="{{ asset('storage/smp.jpg') }}"
                    onerror="this.onerror=null;this.src='{{ asset('apple-touch-icon.png') }}';"
                    alt="Logo"
                />
                <span>SMP Angkasa<br />Adi Soemarmo</span>
            </div>

            <nav class="sidebar__nav">
                @if ($isAdmin)
                    <div class="sidebar__group">
                        <a class="sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <x-icon name="bar-chart" />
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <div class="sidebar__group">
                        <div class="sidebar__label">PPDB</div>
                        <a class="sidebar__link {{ request()->routeIs('admin.ppdb') ? 'is-active' : '' }}" href="{{ route('admin.ppdb') }}">
                            <x-icon name="file" />
                            <span>Pendaftar</span>
                        </a>
                        <a class="sidebar__link {{ request()->routeIs('admin.periode-ppdb') ? 'is-active' : '' }}" href="{{ route('admin.periode-ppdb') }}">
                            <x-icon name="calendar" />
                            <span>Periode PPDB</span>
                        </a>
                    </div>

                    <div class="sidebar__group">
                        <div class="sidebar__label">Akademik</div>
                        <a class="sidebar__link {{ request()->routeIs('admin.siswa') ? 'is-active' : '' }}" href="{{ route('admin.siswa') }}">
                            <x-icon name="user" />
                            <span>Siswa</span>
                        </a>
                        <a class="sidebar__link {{ request()->routeIs('admin.guru') ? 'is-active' : '' }}" href="{{ route('admin.guru') }}">
                            <x-icon name="users" />
                            <span>Guru</span>
                        </a>
                        <a class="sidebar__link {{ request()->routeIs('admin.kelas') ? 'is-active' : '' }}" href="{{ route('admin.kelas') }}">
                            <x-icon name="building" />
                            <span>Kelas</span>
                        </a>
                        <a class="sidebar__link {{ request()->routeIs('admin.mata-pelajaran') ? 'is-active' : '' }}" href="{{ route('admin.mata-pelajaran') }}">
                            <x-icon name="book" />
                            <span>Mata Pelajaran</span>
                        </a>
                    </div>

                    <div class="sidebar__group">
                        <div class="sidebar__label">Pengelolaan Konten</div>
                        <a class="sidebar__link {{ request()->routeIs('admin.konten.pengumuman') ? 'is-active' : '' }}" href="{{ route('admin.konten.pengumuman') }}">
                            <x-icon name="send" />
                            <span>Pengumuman</span>
                        </a>
                        <a class="sidebar__link {{ request()->routeIs('admin.konten.galeri') ? 'is-active' : '' }}" href="{{ route('admin.konten.galeri') }}">
                            <x-icon name="image" />
                            <span>Galeri</span>
                        </a>
                    </div>
                @endif

                <div class="sidebar__group">
                    @if ($isAdmin)
                        <div class="sidebar__label">Laporan</div>
                    @endif
                    <a class="sidebar__link {{ request()->routeIs('admin.konten.repository') ? 'is-active' : '' }}" href="{{ route('admin.konten.repository') }}">
                        <x-icon name="folder" />
                        <span>Repository</span>
                    </a>
                    @if ($isAdmin)
                        <a class="sidebar__link {{ request()->routeIs('admin.laporan') ? 'is-active' : '' }}" href="{{ route('admin.laporan') }}">
                            <x-icon name="file" />
                            <span>Laporan</span>
                        </a>
                    @endif
                </div>
            </nav>

            <div class="sidebar__footer">
                @if ($canManageUsers)
                    <a class="btn btn--outline btn--block btn--sm" href="{{ route('admin.pengguna.create') }}">Tambah Akun</a>
                @endif

                @if ($user)
                    <div class="row gap-2" style="margin-top: 1rem">
                        <span class="avatar">{{ strtoupper(Str::charAt($user->name, 0)) }}</span>
                        <div style="min-width: 0">
                            <div class="text-sm font-semibold nowrap" style="overflow: hidden; text-overflow: ellipsis">{{ $user->name }}</div>
                            <div class="text-xs muted nowrap" style="overflow: hidden; text-overflow: ellipsis">{{ $user->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin-top: 0.75rem">
                        @csrf
                        <button type="submit" class="btn btn--ghost btn--sm btn--block">
                            <x-icon name="log-out" class="text-sm" />
                            Keluar
                        </button>
                    </form>
                @endif
            </div>
        </aside>

        <div class="admin-main">
            <header class="topbar">
                <button type="button" class="topbar__toggle" id="sidebar-toggle" aria-label="Buka sidebar">
                    <x-icon name="menu" />
                </button>
                <div class="topbar__title">@yield('title', 'Dashboard')</div>
                <div class="topbar__user">
                    <a class="nav-link" href="{{ route('profile.edit') }}">Pengaturan</a>
                    <span class="avatar">{{ $user ? strtoupper(Str::charAt($user->name, 0)) : '?' }}</span>
                </div>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert--success" style="margin-bottom: 1.5rem">
                        <x-icon name="check-circle" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const sidebar = document.getElementById('sidebar');
                const backdrop = document.getElementById('sidebar-backdrop');
                const toggle = document.getElementById('sidebar-toggle');
                const close = () => {
                    sidebar.classList.remove('is-open');
                    backdrop.classList.remove('is-open');
                };
                if (toggle) toggle.addEventListener('click', () => {
                    sidebar.classList.toggle('is-open');
                    backdrop.classList.toggle('is-open');
                });
                if (backdrop) backdrop.addEventListener('click', close);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') close();
                });
            })();
        </script>
    @endpush
@endsection
