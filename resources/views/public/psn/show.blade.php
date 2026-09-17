@extends('layouts.public')

@section('content')
<div class="space-y-6">
    <a href="{{ route('psn.index') }}" class="text-sm text-blue-800 hover:underline">&larr; Kembali ke Daftar PSN</a>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-blue-900">{{ $profil->nama_psn }}</h1>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            @if ($profil->nama_klaster)
                <span class="rounded-full bg-blue-100 text-blue-800 px-3 py-1">{{ $profil->nama_klaster }}</span>
            @endif
            @if ($profil->status_psn)
                <span class="rounded-full bg-green-100 text-green-800 px-3 py-1">{{ $profil->status_psn }}</span>
            @endif
            @if ($profil->tipe_hierarki)
                <span class="rounded-full bg-gray-100 text-gray-700 px-3 py-1">{{ $profil->tipe_hierarki }}</span>
            @endif
            @if ($profil->kategori_usulan)
                <span class="rounded-full bg-purple-100 text-purple-700 px-3 py-1">{{ $profil->kategori_usulan }}</span>
            @endif
        </div>

        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Lokasi</dt>
                <dd class="mt-0.5">{{ $profil->nama_provinsi ?? '-' }}@if($profil->kabupaten_kota && $profil->kabupaten_kota !== $profil->nama_provinsi), {{ $profil->kabupaten_kota }}@endif</dd>
            </div>
            <div>
                <dt class="text-gray-500">Target Penyelesaian</dt>
                <dd class="mt-0.5">{{ $profil->tahun_penyelesaian ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Tujuan Utama</dt>
                <dd class="mt-0.5">{{ $profil->tujuan_utama ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Urgensi & Dasar Hukum</dt>
                <dd class="mt-0.5">{{ $profil->urgensi ?? '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Output Akhir</dt>
                <dd class="mt-0.5">{{ $profil->output_akhir ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Pengusul</dt>
                <dd class="mt-0.5">{{ $profil->pengusul ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Pengelola</dt>
                <dd class="mt-0.5">{{ $profil->pengelola ?? '-' }}</dd>
            </div>
        </dl>

        @if ($profil->diagram_kelembagaan_path)
            <div class="mt-6 border-t pt-4">
                <dt class="text-gray-500 text-sm mb-2">Visualisasi Kerangka Kelembagaan</dt>
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($profil->diagram_kelembagaan_path) }}" alt="Diagram Kerangka Kelembagaan {{ $profil->nama_psn }}" class="max-w-full rounded border">
            </div>
        @endif
    </div>

    @if ($indikator->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-700 mb-4">Indikator Output/Outcome (Capaian Terkini)</h2>
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr>
                        <th class="py-2 pr-4">Indikator</th>
                        <th class="py-2 pr-4">Tahun</th>
                        <th class="py-2 pr-4">Target</th>
                        <th class="py-2 pr-4">Realisasi</th>
                        <th class="py-2">% Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($indikator as $i)
                        <tr>
                            <td class="py-2 pr-4">{{ $i->nama_indikator }} @if($i->satuan)({{ $i->satuan }})@endif</td>
                            <td class="py-2 pr-4">{{ $i->tahun }}</td>
                            <td class="py-2 pr-4">{{ $i->target ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $i->realisasi ?? '-' }}</td>
                            <td class="py-2">{{ $i->persen_realisasi !== null ? $i->persen_realisasi.'%' : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
