<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard PSN' }} — Kementerian PPN/Bappenas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <header class="bg-blue-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                <span class="bg-white rounded px-2 py-1.5 shadow-sm shrink-0">
                    <x-application-logo class="h-7 w-auto" />
                </span>
                <span class="font-bold text-lg leading-tight">Dashboard PSN<br><span class="text-xs font-normal text-blue-200">Tim Koordinasi Perencanaan &amp; Pengendalian PSN</span></span>
            </a>
            <nav class="hidden sm:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('beranda') }}" class="hover:text-blue-200 {{ request()->routeIs('beranda') ? 'text-blue-200' : '' }}">Beranda</a>
                <a href="{{ route('psn.index') }}" class="hover:text-blue-200 {{ request()->routeIs('psn.*') ? 'text-blue-200' : '' }}">Daftar PSN</a>
                <a href="{{ route('statistik') }}" class="hover:text-blue-200 {{ request()->routeIs('statistik') ? 'text-blue-200' : '' }}">Statistik</a>
                <a href="{{ route('peta') }}" class="hover:text-blue-200 {{ request()->routeIs('peta') ? 'text-blue-200' : '' }}">Peta Sebaran</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded bg-blue-700 px-3 py-1.5 hover:bg-blue-600">Admin</a>
                @else
                    <a href="{{ route('login') }}" class="rounded bg-blue-700 px-3 py-1.5 hover:bg-blue-600">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="border-t mt-12 py-6 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Tim Koordinasi Perencanaan dan Pengendalian Proyek Strategis Nasional — Kementerian PPN/Bappenas
    </footer>
</body>
</html>
