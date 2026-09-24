<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard PSN' }} — Kementerian PPN/Bappenas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <header class="sticky top-0 z-30 bg-blue-900/95 backdrop-blur text-white border-b border-t-4 border-t-gold-500 border-blue-950 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3">
                <span class="bg-white rounded-lg px-2 py-1.5 shadow-sm shrink-0">
                    <x-application-logo class="h-7 w-auto" />
                </span>
                <span class="font-bold text-lg leading-tight">Dashboard PSN<br><span class="text-xs font-normal text-blue-200">Tim Koordinasi Perencanaan &amp; Pengendalian PSN</span></span>
            </a>
            <nav class="hidden sm:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('beranda') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('beranda') ? 'bg-white/15 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">Beranda</a>
                <a href="{{ route('psn.index') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('psn.*') ? 'bg-white/15 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">Daftar PSN</a>
                <a href="{{ route('statistik') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('statistik') ? 'bg-white/15 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">Statistik</a>
                <a href="{{ route('peta') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('peta') ? 'bg-white/15 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">Peta Sebaran</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="ms-2 rounded-lg bg-gold-500 text-blue-950 font-semibold px-3.5 py-1.5 shadow-sm hover:bg-gold-400 transition-colors">Admin</a>
                @else
                    <a href="{{ route('login') }}" class="ms-2 rounded-lg bg-gold-500 text-blue-950 font-semibold px-3.5 py-1.5 shadow-sm hover:bg-gold-400 transition-colors">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 sm:grid-cols-3 gap-8 text-sm">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <x-application-logo class="h-6 w-auto" />
                </div>
                <p class="text-gray-500 leading-relaxed">
                    Instrumen tunggal pemantauan &amp; pengendalian Proyek Strategis Nasional,
                    Tim Koordinasi Perencanaan dan Pengendalian PSN — Kementerian PPN/Bappenas.
                </p>
            </div>
            <div>
                <div class="font-semibold text-gray-700 mb-3">Tautan</div>
                <ul class="space-y-2 text-gray-500">
                    <li><a href="{{ route('beranda') }}" class="hover:text-blue-800 transition-colors">Beranda</a></li>
                    <li><a href="{{ route('psn.index') }}" class="hover:text-blue-800 transition-colors">Daftar PSN</a></li>
                    <li><a href="{{ route('statistik') }}" class="hover:text-blue-800 transition-colors">Statistik</a></li>
                    <li><a href="{{ route('peta') }}" class="hover:text-blue-800 transition-colors">Peta Sebaran</a></li>
                </ul>
            </div>
            <div>
                <div class="font-semibold text-gray-700 mb-3">Sumber Data</div>
                <ul class="space-y-2 text-gray-500">
                    <li>RKP Pemutakhiran (Perpres 68)</li>
                    <li>Data PEKS3 &amp; Data PSI</li>
                    <li>Permenko</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-100 py-5 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Tim Koordinasi Perencanaan dan Pengendalian Proyek Strategis Nasional — Kementerian PPN/Bappenas
        </div>
    </footer>
</body>
</html>
