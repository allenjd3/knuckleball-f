<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="shortcut icon" href={{ asset('favicon-32x32.png') }} />

        <!-- Fonts -->
        <link rel="preconnect" href="https://use.typekit.net">
        <link rel="preconnect" href="https://fonts.bunny.net">

        <link href="https://use.typekit.net/hfy1qol.css" rel="stylesheet">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @filamentStyles
        @vite('resources/css/app.css')
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen flex flex-col">
            @livewire('navigation-menu')

            <main class="grow">
                {{ $slot }}
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-500">
                    <span>&copy; {{ date('Y') }} Knuckleball. All rights reserved.</span>
                    <a href="{{ route('dmca') }}" class="hover:text-gray-700 underline">DMCA Policy</a>
                </div>
            </footer>
        </div>

        @stack('modals')

        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')

        @stack('scripts')
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-899C14WKNP"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-899C14WKNP');
        </script>
    </body>
</html>
