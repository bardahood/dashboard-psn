<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analisis Carryover RKP 2027</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Menyandingkan lampiran resmi <strong>"Daftar PSN dalam RKP 2027"</strong> dengan data PSN dashboard
                (hasil impor Matrik Sandingan, bersumber dari RKP Pemutakhiran 2026/Perpres 68). Pencocokan nama
                proyek bersifat otomatis (fuzzy matching) sehingga hasil pada kategori "Perlu Ditinjau" dan
                "Tidak Ditemukan Lagi" perlu diverifikasi manual sebelum dijadikan dasar keputusan.
            </p>

            @if (session('status'))
                <div class="bg-green-50 text-green-800 text-sm rounded-lg p-4">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 text-red-800 text-sm rounded-lg p-4">{{ session('error') }}</div>
            @endif

            @if (! $hasil)
                <div class="bg-yellow-50 text-yellow-800 text-sm rounded-lg p-4">
                    Berkas "Daftar PSN dalam RKP 2027" (.docx) belum tersedia di server.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white shadow rounded-lg p-4">
                        <div class="text-3xl font-bold text-green-700">{{ $hasil['carryover']->count() }}</div>
                        <div class="text-sm text-gray-500">Carryover ke RKP 2027 (cocok dengan data existing, skor &ge; {{ $ambangSkor }})</div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <div class="text-3xl font-bold text-yellow-700">{{ $hasil['perlu_ditinjau']->count() }}</div>
                        <div class="text-sm text-gray-500">Perlu ditinjau manual (redaksional berbeda / kemungkinan usulan baru)</div>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <div class="text-3xl font-bold text-red-700">{{ $hasil['tidak_ditemukan_lagi']->count() }}</div>
                        <div class="text-sm text-gray-500">PSN existing tidak ditemukan lagi di RKP 2027 (kandidat evaluasi keluar)</div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-4 flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-600">
                        Terapkan kategori <strong>Carryover</strong> pada kolom "Kategori Usulan" untuk
                        {{ $jumlahBelumBerkategori }} PSN yang cocok di atas dan belum berkategori
                        (isian manual yang sudah ada tidak akan ditimpa).
                    </p>
                    <form method="POST" action="{{ route('admin.analisis-rkp2027.terapkan') }}" onsubmit="return confirm('Terapkan kategori Carryover ke {{ $jumlahBelumBerkategori }} PSN?');">
                        @csrf
                        <button type="submit" class="rounded-md bg-blue-800 text-white text-sm font-medium px-4 py-2 hover:bg-blue-700 whitespace-nowrap" @disabled($jumlahBelumBerkategori === 0)>
                            Terapkan Kategori Carryover
                        </button>
                    </form>
                </div>

                <div class="bg-white shadow rounded-lg overflow-x-auto">
                    <div class="px-4 py-3 border-b font-semibold text-gray-700">Perlu Ditinjau Manual ({{ $hasil['perlu_ditinjau']->count() }})</div>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-2">Klaster (RKP 2027)</th>
                                <th class="px-4 py-2">Nama Proyek/Program (RKP 2027)</th>
                                <th class="px-4 py-2">Dugaan Kecocokan Terdekat di Dashboard</th>
                                <th class="px-4 py-2">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($hasil['perlu_ditinjau'] as $m)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $m['klaster'] ?? '-' }}</td>
                                    <td class="px-4 py-2 max-w-md">{{ $m['nama_proyek'] }}</td>
                                    <td class="px-4 py-2 max-w-md">{{ $m['psn_nama'] ?? '(tidak ada kandidat)' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $m['skor'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Tidak ada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white shadow rounded-lg overflow-x-auto">
                    <div class="px-4 py-3 border-b font-semibold text-gray-700">Tidak Ditemukan Lagi di RKP 2027 ({{ $hasil['tidak_ditemukan_lagi']->count() }})</div>
                    <p class="px-4 pt-3 text-xs text-gray-400">
                        PSN ini ada di data dashboard (RKP Pemutakhiran 2026) tetapi tidak terdeteksi pada lampiran RKP 2027 --
                        kandidat untuk ditindaklanjuti lewat menu Evaluasi Status/Rekomendasi Keluar dari Daftar PSN.
                    </p>
                    <table class="min-w-full text-sm mt-2">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-2">Nama PSN</th>
                                <th class="px-4 py-2">Klaster</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($hasil['tidak_ditemukan_lagi'] as $psn)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $psn->nama_psn }}</td>
                                    <td class="px-4 py-2">{{ $psn->klaster?->nama_klaster ?? '-' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <a href="{{ route('admin.psn.show', $psn) }}" class="text-blue-800 hover:underline">Lihat Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Tidak ada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
