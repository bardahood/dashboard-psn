@extends('layouts.public')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-blue-900">Daftar Proyek Strategis Nasional</h1>

    <form method="GET" class="bg-white rounded-lg shadow p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama PSN..."
               class="col-span-1 sm:col-span-2 rounded-md border-gray-300 text-sm">
        <select name="klaster_id" class="rounded-md border-gray-300 text-sm">
            <option value="">Semua Klaster</option>
            @foreach ($klaster as $k)
                <option value="{{ $k->id }}" @selected(request('klaster_id') == $k->id)>{{ $k->nama_klaster }}</option>
            @endforeach
        </select>
        <select name="provinsi_id" class="rounded-md border-gray-300 text-sm">
            <option value="">Semua Provinsi</option>
            @foreach ($provinsi as $p)
                <option value="{{ $p->id }}" @selected(request('provinsi_id') == $p->id)>{{ $p->nama_provinsi }}</option>
            @endforeach
        </select>
        <select name="status_psn_id" class="rounded-md border-gray-300 text-sm">
            <option value="">Semua Status</option>
            @foreach ($status as $s)
                <option value="{{ $s->id }}" @selected(request('status_psn_id') == $s->id)>{{ $s->nama_status }}</option>
            @endforeach
        </select>
        <select name="instansi_id" class="col-span-1 sm:col-span-3 rounded-md border-gray-300 text-sm">
            <option value="">Semua K/L Penanggung Jawab</option>
            @foreach ($instansi as $i)
                <option value="{{ $i->id }}" @selected(request('instansi_id') == $i->id)>{{ $i->nama_instansi }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-blue-800 text-white text-sm font-medium py-2 hover:bg-blue-700">Filter</button>
        @if (request()->anyFilled(['q', 'klaster_id', 'provinsi_id', 'status_psn_id', 'instansi_id']))
            <a href="{{ route('psn.index') }}" class="text-sm text-gray-500 self-center hover:underline">Reset filter</a>
        @endif
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nama PSN</th>
                    <th class="px-4 py-3">Klaster</th>
                    <th class="px-4 py-3">Provinsi</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Target Selesai</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($daftarPsn as $psn)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('psn.show', $psn) }}" class="text-blue-800 font-medium hover:underline">
                                {{ \Illuminate\Support\Str::limit($psn->nama_psn, 80) }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $psn->klaster?->nama_klaster ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $psn->provinsi?->nama_provinsi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $psn->statusPsn?->nama_status ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $psn->tahun_penyelesaian ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada data PSN.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $daftarPsn->links() }}
</div>
@endsection
