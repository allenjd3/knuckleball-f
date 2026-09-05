<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@stack('page-title'){{ config('app.name', 'Knuckleball') }}</title>
        @stack('head')
        <link rel="shortcut icon" href={{ asset('favicon-32x32.png') }} />

        <!-- Fonts -->
        <link rel="preconnect" href="https://use.typekit.net">
        <link rel="preconnect" href="https://fonts.bunny.net">

        <link href="https://use.typekit.net/hfy1qol.css" rel="stylesheet">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Google Maps -->
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&loading=async"></script>
        <script>
            window.knuckleballMapStyle = [
                {"featureType":"all","elementType":"labels.text.fill","stylers":[{"color":"#7c93a3"},{"lightness":"-10"}]},
                {"featureType":"administrative.country","elementType":"geometry","stylers":[{"visibility":"on"}]},
                {"featureType":"administrative.country","elementType":"geometry.stroke","stylers":[{"color":"#a0a4a5"}]},
                {"featureType":"administrative.province","elementType":"geometry.stroke","stylers":[{"color":"#62838e"}]},
                {"featureType":"landscape","elementType":"geometry.fill","stylers":[{"color":"#dde3e3"}]},
                {"featureType":"landscape.man_made","elementType":"geometry.stroke","stylers":[{"color":"#3f4a51"},{"weight":"0.30"}]},
                {"featureType":"poi","elementType":"all","stylers":[{"visibility":"simplified"}]},
                {"featureType":"poi.attraction","elementType":"all","stylers":[{"visibility":"on"}]},
                {"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},
                {"featureType":"poi.government","elementType":"all","stylers":[{"visibility":"off"}]},
                {"featureType":"poi.park","elementType":"all","stylers":[{"visibility":"on"}]},
                {"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#a9de83"}]},
                {"featureType":"poi.park","elementType":"geometry.stroke","stylers":[{"color":"#bae6a1"}]},
                {"featureType":"poi.sports_complex","elementType":"all","stylers":[{"visibility":"on"}]},
                {"featureType":"poi.sports_complex","elementType":"geometry.fill","stylers":[{"color":"#c6e8b3"}]},
                {"featureType":"poi.sports_complex","elementType":"geometry.stroke","stylers":[{"color":"#bae6a1"}]},
                {"featureType":"road","elementType":"all","stylers":[{"saturation":"-100"},{"visibility":"on"}]},
                {"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#a9b4b8"}]},
                {"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#f5f5f5"}]},
                {"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#d6d6d6"}]},
                {"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},
                {"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},
                {"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},
                {"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#a3c7df"}]}
            ];
        </script>

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

        <x-lightbox />

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
