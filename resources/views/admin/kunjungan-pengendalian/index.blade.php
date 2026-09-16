<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kunjungan Lapangan: Pengendalian
                @if ($psnTerpilih)
                    <span class="text-sm font-normal text-gray-500">— {{ \Illuminate\Support\Str::limit($psnTerpilih->nama_psn, 50) }}</span>
                @endif
            </h2>
            <a href="{{ route('admin.kunjungan-pengendalian.create', $psnTerpilih ? ['psn_id' => $psnTerpilih->id] : []) }}"
               class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                + Kunjungan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">PSN</th>
                            <th class="px-4 py-3">Tanggal Kunjungan</th>
                            <th class="px-4 py-3">Verifikator</th>
                            <th class="px-4 py-3">Skor</th>
                            <th class="px-4 py-3">Status Pengendalian</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftarKunjungan as $k)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($k->psn?->nama_psn, 50) }}</td>
                                <td class="px-4 py-3">{{ optional($k->tanggal_kunjungan)->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $k->verifikator?->nama_pic ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $k->skorKeseluruhan() ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($k->status_pengendalian)
                                        <span class="rounded-full text-xs px-2 py-1 bg-gray-100 text-gray-700">{{ $k->status_pengendalian }}</span>
                                    @else - @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.kunjungan-pengendalian.edit', $k) }}" class="text-blue-800 hover:underline">Lanjutkan/Ubah</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada data kunjungan pengendalian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarKunjungan->links() }}
        </div>
    </div>
</x-app-layout>
