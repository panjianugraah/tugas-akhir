<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>@yield('title', config('app.name', 'SMP Angkasa Adi Soemarmo'))</title>

        <script>
            (function () {
                const appearance = '{{ $appearance ?? 'system' }}';
                if (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        <style>
            html { background-color: #ffffff; }
            html.dark { background-color: #18181b; }
        </style>

        @vite(['resources/css/app.css'])
        @fonts

        @stack('head')
    </head>
    <body>
        @yield('body')
        @stack('scripts')
    </body>
</html>
