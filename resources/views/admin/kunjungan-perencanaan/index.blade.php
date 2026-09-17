<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kunjungan Lapangan: Perencanaan</h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.verifikasi-usulan') }}" class="text-sm text-blue-800 hover:underline">
                    Rekap Kelengkapan &amp; Verifikasi &rarr;
                </a>
                <a href="{{ route('admin.kunjungan-perencanaan.create') }}" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                    + Kunjungan Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" class="bg-white rounded-lg shadow p-4 flex gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama usulan..."
                       class="flex-1 rounded-md border-gray-300 text-sm">
                <button class="rounded-md bg-gray-700 text-white text-sm font-medium px-4 hover:bg-gray-600">Cari</button>
            </form>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama Usulan</th>
                            <th class="px-4 py-3">Klaster</th>
                            <th class="px-4 py-3">Jenis Pengusul</th>
                            <th class="px-4 py-3">Tanggal Kunjungan</th>
                            <th class="px-4 py-3">Rekomendasi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftarKunjungan as $k)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($k->nama_usulan_psn, 50) }}</td>
                                <td class="px-4 py-3">{{ $k->klaster?->nama_klaster ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $k->jenis_pengusul ?? '-' }}</td>
                                <td class="px-4 py-3">{{ optional($k->tanggal_kunjungan)->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($k->rekomendasi_keseluruhan)
                                        <span class="rounded-full text-xs px-2 py-1 bg-gray-100 text-gray-700">{{ $k->rekomendasi_keseluruhan }}</span>
                                    @else - @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.kunjungan-perencanaan.edit', $k) }}" class="text-blue-800 hover:underline">Lanjutkan/Ubah</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada data kunjungan perencanaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarKunjungan->links() }}
        </div>
    </div>
</x-app-layout>
