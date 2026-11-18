<header class="site-header">
    <nav class="site-nav" aria-label="Navigasi utama">
        <a href="{{ url('/') }}" class="site-nav__brand" aria-label="Beranda SMP Angkasa Adi Soemarmo">
            <img
                src="{{ asset('storage/smp.jpg') }}"
                onerror="this.onerror=null;this.src='{{ asset('apple-touch-icon.png') }}';"
                alt="Logo SMP Angkasa Adi Soemarmo"
                class="site-nav__logo"
            />
            <span class="site-nav__name">SMP Angkasa<br />Adi Soemarmo</span>
        </a>

        <div class="site-nav__links">
            <a class="nav-link" href="{{ url('/#home') }}">Beranda</a>
            <a class="nav-link" href="{{ url('/#about') }}">Tentang</a>
            <a class="nav-link" href="{{ url('/#vision') }}">Visi &amp; Misi</a>
            <a class="nav-link" href="{{ url('/#programs') }}">Program</a>
            <a class="nav-link" href="{{ route('galeri.publik') }}">Galeri</a>
            <a class="nav-link" href="{{ route('pengumuman.publik.index') }}">Pengumuman</a>
            <a class="nav-link" href="{{ route('ppdb') }}">PPDB</a>
            <a class="nav-link" href="{{ route('repository.publik') }}">Repository</a>
        </div>

        <div class="site-nav__actions">
            <a class="btn btn--brand" style="min-height: 2.75rem; padding-inline: 1.25rem" href="{{ route('login') }}">Login</a>
            <button type="button" class="nav-toggle" id="nav-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navbar-mobile">
                <x-icon name="menu" style="width: 1.5rem; height: 1.5rem" />
            </button>
        </div>
    </nav>

    <div class="mobile-menu" id="navbar-mobile" hidden>
        <a href="{{ url('/#home') }}">Beranda</a>
        <a href="{{ url('/#about') }}">Tentang</a>
        <a href="{{ url('/#vision') }}">Visi &amp; Misi</a>
        <a href="{{ url('/#programs') }}">Program</a>
        <a href="{{ route('galeri.publik') }}">Galeri</a>
        <a href="{{ route('pengumuman.publik.index') }}">Pengumuman</a>
        <a href="{{ route('ppdb') }}">PPDB</a>
        <a href="{{ route('repository.publik') }}">Repository</a>
    </div>
</header>

@push('scripts')
    <script>
        (function () {
            const toggle = document.getElementById('nav-toggle');
            const menu = document.getElementById('navbar-mobile');
            if (!toggle || !menu) return;
            const close = () => {
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Buka menu');
                toggle.innerHTML = @json('<svg class="icon" style="width:1.5rem;height:1.5rem" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>');
            };
            toggle.addEventListener('click', () => {
                if (menu.hidden) {
                    menu.hidden = false;
                    toggle.setAttribute('aria-expanded', 'true');
                    toggle.setAttribute('aria-label', 'Tutup menu');
                    toggle.innerHTML = @json('<svg class="icon" style="width:1.5rem;height:1.5rem" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>');
                } else {
                    close();
                }
            });
            menu.addEventListener('click', close);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') close();
            });
        })();
    </script>
@endpush
