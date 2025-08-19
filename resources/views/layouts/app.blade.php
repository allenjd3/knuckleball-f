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

        <div class="min-h-screen">
            @livewire('navigation-menu')

            <!-- Page Heading -->

            {{ $slot }}
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
