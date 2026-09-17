<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Log</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="bg-white rounded-lg shadow p-4 flex gap-3">
                <select name="nama_tabel" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua Tabel</option>
                    @foreach ($daftarTabel as $tabel)
                        <option value="{{ $tabel }}" @selected(request('nama_tabel') === $tabel)>{{ $tabel }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-gray-700 text-white text-sm font-medium px-4 hover:bg-gray-600">Filter</button>
            </form>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Tabel</th>
                            <th class="px-4 py-3">Record ID</th>
                            <th class="px-4 py-3">Aksi</th>
                            <th class="px-4 py-3">Oleh</th>
                            <th class="px-4 py-3">Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftarAudit as $log)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $log->nama_tabel }}</td>
                                <td class="px-4 py-3">{{ $log->record_id }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full text-xs px-2 py-1
                                        {{ $log->aksi === 'insert' ? 'bg-green-100 text-green-700' : ($log->aksi === 'update' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $log->aksi }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $log->pic?->nama_pic ?? '-' }}</td>
                                <td class="px-4 py-3 max-w-md">
                                    @if ($log->aksi === 'update' && $log->nilai_baru)
                                        <ul class="text-xs space-y-0.5">
                                            @foreach ($log->nilai_baru as $field => $value)
                                                <li><span class="text-gray-500">{{ $field }}:</span> {{ \Illuminate\Support\Str::limit((string) ($log->nilai_lama[$field] ?? '-'), 25) }} &rarr; {{ \Illuminate\Support\Str::limit((string) $value, 25) }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-xs text-gray-400">{{ $log->aksi === 'insert' ? 'Data baru dibuat' : 'Data dihapus' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada catatan audit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarAudit->links() }}
        </div>
    </div>
</x-app-layout>
