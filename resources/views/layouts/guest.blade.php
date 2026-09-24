<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>

            <div class="relative bg-white rounded-xl px-4 py-3 shadow-lg">
                <a href="/">
                    <x-application-logo class="h-12 w-auto" />
                </a>
            </div>

            <div class="relative w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-xl ring-1 ring-gray-950/5 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="relative mt-6 text-xs text-blue-200/70 text-center px-6">
                Dashboard PSN &middot; Tim Koordinasi Perencanaan dan Pengendalian PSN &middot; Kementerian PPN/Bappenas
            </p>
        </div>
    </body>
</html>
