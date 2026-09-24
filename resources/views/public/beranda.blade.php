@extends('layouts.public')

@section('content')
<div class="space-y-10">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-blue-900 to-blue-700 px-6 py-14 sm:py-16 text-center shadow-xl">
        <div class="absolute inset-0 opacity-[0.08] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative">
            <span class="inline-block rounded-full bg-white/10 text-blue-100 text-xs font-semibold tracking-wide uppercase px-3 py-1 mb-4">
                Kementerian PPN/Bappenas
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight">Dashboard Proyek Strategis Nasional</h1>
            <p class="mt-4 text-blue-100/90 max-w-2xl mx-auto leading-relaxed">
                Instrumen tunggal pemantauan dan pengendalian pelaksanaan seluruh Proyek Strategis Nasional (PSN),
                merekonsiliasi data RKP Pemutakhiran, PEKS3, PSI, dan Permenko.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('psn.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gold-500 px-6 py-3 text-blue-950 font-semibold shadow-sm hover:bg-gold-400 transition-colors">
                    Lihat Daftar PSN
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
                <a href="{{ route('peta') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-6 py-3 text-white font-medium ring-1 ring-white/20 hover:bg-white/15 transition-colors">
                    Peta Sebaran
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 text-center relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-blue-800"></div>
            <div class="text-4xl font-bold text-blue-900">{{ $ringkasan['total_psn'] }}</div>
            <div class="mt-1 text-sm text-gray-500">Total PSN Tercatat</div>
        </div>
        <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 text-center relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-gold-500"></div>
            <div class="text-4xl font-bold text-blue-900">{{ $ringkasan['per_klaster']->count() }}</div>
            <div class="mt-1 text-sm text-gray-500">Klaster PSN</div>
        </div>
        <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 text-center relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-blue-400"></div>
            <div class="text-4xl font-bold text-blue-900">{{ $ringkasan['per_status']->count() }}</div>
            <div class="mt-1 text-sm text-gray-500">Tahap Lifecycle</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Sebaran per Klaster</h2>
            <ul class="divide-y divide-gray-100 text-sm">
                @foreach ($ringkasan['per_klaster'] as $k)
                    <li class="flex justify-between items-center py-2.5">
                        <span class="text-gray-600">{{ $k->nama_klaster }}</span>
                        <span class="inline-flex items-center justify-center min-w-[2rem] rounded-full bg-blue-50 text-blue-800 font-semibold px-2 py-0.5 text-xs">{{ $k->psn_count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Status Lifecycle PSN</h2>
            <ul class="divide-y divide-gray-100 text-sm">
                @foreach ($ringkasan['per_status'] as $s)
                    <li class="flex justify-between items-center py-2.5">
                        <span class="text-gray-600">{{ $s->nama_status }}</span>
                        <span class="inline-flex items-center justify-center min-w-[2rem] rounded-full bg-blue-50 text-blue-800 font-semibold px-2 py-0.5 text-xs">{{ $s->psn_count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
