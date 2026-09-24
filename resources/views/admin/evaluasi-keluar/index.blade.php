<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekomendasi Keluar dari Daftar PSN</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Rekapitulasi PSN yang pada evaluasi status tahunan ditandai <strong>"tidak lagi membutuhkan status PSN"</strong>
                beserta justifikasinya (Bagian 3c KAK Laporan Penyusunan Daftar PSN) -- diambil dari data
                Kebutuhan Status PSN Tahun Selanjutnya pada profil masing-masing PSN, bukan tabel terpisah.
            </p>

            <form method="GET" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4 flex flex-wrap items-center gap-3">
                <select name="tahun_evaluasi" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">Semua Tahun</option>
                    @foreach ($tahunOptions as $tahun)
                        <option value="{{ $tahun }}" @selected(request('tahun_evaluasi') == $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-gray-700 text-white shadow-sm transition-all text-sm font-medium px-4 py-2 hover:bg-gray-600">Filter</button>
                @if (request()->filled('tahun_evaluasi'))
                    <a href="{{ route('admin.evaluasi-keluar') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
                @endif
            </form>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Tahun Evaluasi</th>
                            <th class="px-4 py-3">Nama PSN</th>
                            <th class="px-4 py-3">Klaster</th>
                            <th class="px-4 py-3">Justifikasi</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftarRekomendasi as $evaluasi)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $evaluasi->tahun_evaluasi }}</td>
                                <td class="px-4 py-3">{{ $evaluasi->psn?->nama_psn ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $evaluasi->psn?->klaster?->nama_klaster ?? '-' }}</td>
                                <td class="px-4 py-3 max-w-lg">{{ $evaluasi->justifikasi ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($evaluasi->psn)
                                        <a href="{{ route('admin.psn.penjabaran', $evaluasi->psn) }}" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Lihat Detail</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada PSN yang direkomendasikan keluar dari daftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarRekomendasi->links() }}
        </div>
    </div>
</x-app-layout>
