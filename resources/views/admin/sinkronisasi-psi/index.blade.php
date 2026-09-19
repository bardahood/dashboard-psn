<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sinkronisasi API PSI</h2>
            <form method="POST" action="{{ route('admin.sinkronisasi-psi.trigger') }}">
                @csrf
                <button class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                    Jalankan Sinkronisasi Sekarang
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-amber-50 text-amber-700 px-4 py-3 text-sm">{{ session('error') }}</div>
            @endif

            <p class="text-sm text-gray-500">
                Menarik pemutakhiran data PSN dari API Direktorat Pembiayaan Strategis dan Inovatif (Dit. PSI).
                Endpoint API resmi belum tersedia -- lihat catatan pada <code>App\Services\PsiSyncService</code>.
            </p>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Tanggal Sync</th>
                            <th class="px-4 py-3">Jumlah PSN Diterima</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($riwayat as $log)
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->tanggal_sync->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $log->jumlah_psn_diterima ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full text-xs px-2.5 py-1 font-medium
                                        {{ $log->status === 'Sukses' ? 'bg-green-100 text-green-700' : ($log->status === 'Sebagian' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-lg">{{ $log->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada riwayat sinkronisasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $riwayat->links() }}
        </div>
    </div>
</x-app-layout>
