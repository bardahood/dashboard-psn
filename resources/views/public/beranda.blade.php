@extends('layouts.public')

@section('content')
<div class="space-y-10">
    <div class="text-center py-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-blue-900">Dashboard Proyek Strategis Nasional</h1>
        <p class="mt-3 text-gray-600 max-w-2xl mx-auto">
            Instrumen tunggal pemantauan dan pengendalian pelaksanaan seluruh Proyek Strategis Nasional (PSN),
            merekonsiliasi data RKP Pemutakhiran, PEKS3, PSI, dan Permenko.
        </p>
        <a href="{{ route('psn.index') }}" class="inline-block mt-6 rounded-md bg-blue-800 px-6 py-3 text-white font-medium hover:bg-blue-700">
            Lihat Daftar PSN
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-4xl font-bold text-blue-900">{{ $ringkasan['total_psn'] }}</div>
            <div class="mt-1 text-sm text-gray-500">Total PSN Tercatat</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-4xl font-bold text-blue-900">{{ $ringkasan['per_klaster']->count() }}</div>
            <div class="mt-1 text-sm text-gray-500">Klaster PSN</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-4xl font-bold text-blue-900">{{ $ringkasan['per_status']->count() }}</div>
            <div class="mt-1 text-sm text-gray-500">Tahap Lifecycle</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Sebaran per Klaster</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($ringkasan['per_klaster'] as $k)
                    <li class="flex justify-between border-b py-1.5">
                        <span>{{ $k->nama_klaster }}</span>
                        <span class="font-semibold">{{ $k->psn_count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Status Lifecycle PSN</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($ringkasan['per_status'] as $s)
                    <li class="flex justify-between border-b py-1.5">
                        <span>{{ $s->nama_status }}</span>
                        <span class="font-semibold">{{ $s->psn_count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
