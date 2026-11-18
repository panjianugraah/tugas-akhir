@extends('layouts.settings')

@section('settings-content')
    <section class="card">
        <div class="card__header">
            <div>
                <h2 class="card__title">Pengaturan tampilan</h2>
                <p class="card__subtitle">Perbarui pengaturan tampilan untuk akun Anda</p>
            </div>
            <x-icon name="settings" />
        </div>

        <div class="card__body">
            <div class="appearance-tabs" role="group" aria-label="Pilih tampilan">
                <button type="button" class="appearance-tab" data-appearance="light" aria-pressed="false">
                    <x-icon name="sun" />
                    <span>Terang</span>
                </button>
                <button type="button" class="appearance-tab" data-appearance="dark" aria-pressed="false">
                    <x-icon name="moon" />
                    <span>Gelap</span>
                </button>
                <button type="button" class="appearance-tab is-active" data-appearance="system" aria-pressed="true">
                    <x-icon name="monitor" />
                    <span>Sistem</span>
                </button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const tabs = Array.from(document.querySelectorAll('[data-appearance]'));
            const media = window.matchMedia('(prefers-color-scheme: dark)');

            const getAppearance = function () {
                const match = document.cookie.match(/(?:^|;\s*)appearance=([^;]*)/);
                const value = match ? match[1] : 'system';

                return value === 'light' || value === 'dark' || value === 'system' ? value : 'system';
            };

            const applyAppearance = function (appearance) {
                const isDark = appearance === 'dark' || (appearance === 'system' && media.matches);

                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

                tabs.forEach(function (tab) {
                    const active = tab.dataset.appearance === appearance;
                    tab.classList.toggle('is-active', active);
                    tab.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            };

            let appearance = getAppearance();

            applyAppearance(appearance);

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    appearance = tab.dataset.appearance;
                    document.cookie = 'appearance=' + appearance + ';path=/;max-age=' + (365 * 24 * 60 * 60) + ';SameSite=Lax';
                    applyAppearance(appearance);
                });
            });

            const onSystemChange = function () {
                if (appearance === 'system') {
                    applyAppearance(appearance);
                }
            };

            if (media.addEventListener) {
                media.addEventListener('change', onSystemChange);
            } else if (media.addListener) {
                media.addListener(onSystemChange);
            }
        })();
    </script>
@endpush
