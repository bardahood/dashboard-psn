<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Kelengkapan Administrasi & Verifikasi Usulan PSN</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Rekapitulasi lintas seluruh Instrumen Kunjungan Perencanaan: kelengkapan 9 dokumen teknis tetap,
                status gate Kriteria Utama, skor keseluruhan, dan rekomendasi otomatis (Bagian 3d KAK Laporan
                Penyusunan Daftar PSN).
            </p>

            <form method="GET" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4 flex gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama usulan/PSN..."
                       class="flex-1 rounded-lg border-gray-300 transition-colors text-sm">
                <button class="rounded-lg bg-gray-700 text-white shadow-sm transition-all text-sm font-medium px-4 hover:bg-gray-600">Cari</button>
            </form>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama Usulan / PSN</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kelengkapan Dokumen</th>
                            <th class="px-4 py-3">Gate Utama</th>
                            <th class="px-4 py-3">Skor</th>
                            <th class="px-4 py-3">Rekomendasi</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftarUsulan as $kunjungan)
                            @php $rekap = $kunjungan->rekap_dokumen; @endphp
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($kunjungan->psn?->nama_psn ?? $kunjungan->nama_usulan_psn, 60) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $kunjungan->tanggal_kunjungan?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-green-700">{{ $rekap['ya'] }} Lengkap</span> ·
                                    <span class="text-amber-600">{{ $rekap['sebagian'] }} Sebagian</span> ·
                                    <span class="text-red-600">{{ $rekap['tidak'] }} Tidak Ada</span>
                                    <span class="text-gray-400">(dari {{ $rekap['total'] }})</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($kunjungan->gateUtamaGagal())
                                        <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-red-100 text-red-700">Gagal</span>
                                    @else
                                        <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-green-100 text-green-700">Lolos</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $kunjungan->skorKeseluruhan() !== null ? number_format($kunjungan->skorKeseluruhan(), 1) : '-' }}</td>
                                <td class="px-4 py-3">{{ $kunjungan->rekomendasiOtomatis() ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('admin.kunjungan-perencanaan.edit', $kunjungan) }}" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada usulan yang diverifikasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarUsulan->links() }}
        </div>
    </div>
</x-app-layout>
