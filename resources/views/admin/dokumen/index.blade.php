<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Dokumen</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Repositori seluruh bukti dukung (dokumentasi) yang diunggah lintas Kunjungan Lapangan Pengendalian,
                agar dapat ditelusuri per-PSN/kategori tanpa membuka wizard satu per satu.
            </p>

            <form method="GET" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-3">
                <input type="text" name="psn" value="{{ request('psn') }}" placeholder="Cari nama PSN..."
                       class="rounded-md border-gray-300 text-sm flex-1 min-w-[200px]">
                <select name="kategori" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-gray-700 text-white text-sm font-medium px-4 hover:bg-gray-600">Filter</button>
                @if (request('psn') || request('kategori'))
                    <a href="{{ route('admin.dokumen') }}" class="text-sm text-gray-500 self-center hover:underline">Reset</a>
                @endif
            </form>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">PSN</th>
                            <th class="px-4 py-3">Tanggal Kunjungan</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">File</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($dokumen as $doc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    @if ($doc->kunjungan?->psn)
                                        <a href="{{ route('admin.psn.show', $doc->kunjungan->psn) }}" class="text-blue-800 hover:underline">
                                            {{ $doc->kunjungan->psn->nama_psn }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $doc->kunjungan?->tanggal_kunjungan?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full text-xs px-2 py-1 bg-blue-50 text-blue-700">{{ $doc->kategori ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $doc->deskripsi ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($doc->nama_file_tautan)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doc->nama_file_tautan) }}"
                                           target="_blank" class="text-blue-800 hover:underline">Lihat/Unduh</a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada dokumen diunggah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $dokumen->links() }}
        </div>
    </div>
</x-app-layout>
