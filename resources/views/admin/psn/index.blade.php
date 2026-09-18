<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Data PSN</h2>
            <div class="flex items-center gap-4">
                @can('profil.manage')
                    <a href="{{ route('admin.evaluasi-keluar') }}" class="text-sm text-blue-800 hover:underline">
                        Rekomendasi Keluar dari Daftar &rarr;
                    </a>
                    <a href="{{ route('admin.analisis-rkp2027') }}" class="text-sm text-blue-800 hover:underline">
                        Analisis Carryover RKP 2027 &rarr;
                    </a>
                @endcan
                @can('create', App\Models\Psn::class)
                    <a href="{{ route('admin.psn.create') }}" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">+ Tambah PSN</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif

            <form method="GET" class="bg-white rounded-lg shadow p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama PSN..."
                       class="col-span-1 sm:col-span-2 rounded-md border-gray-300 text-sm">
                <select name="klaster_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua Klaster</option>
                    @foreach ($klaster as $k)
                        <option value="{{ $k->id }}" @selected(request('klaster_id') == $k->id)>{{ $k->nama_klaster }}</option>
                    @endforeach
                </select>
                <select name="status_psn_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua Status</option>
                    @foreach ($status as $s)
                        <option value="{{ $s->id }}" @selected(request('status_psn_id') == $s->id)>{{ $s->nama_status }}</option>
                    @endforeach
                </select>
                <select name="kategori_usulan" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua Kategori Usulan</option>
                    <option value="Carryover" @selected(request('kategori_usulan') === 'Carryover')>Carryover (PSN Berjalan)</option>
                    <option value="Usulan Baru" @selected(request('kategori_usulan') === 'Usulan Baru')>Usulan Baru</option>
                </select>
                <button class="rounded-md bg-gray-700 text-white text-sm font-medium py-2 hover:bg-gray-600">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama PSN</th>
                            <th class="px-4 py-3">Klaster</th>
                            <th class="px-4 py-3">Provinsi</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Kategori Usulan</th>
                            <th class="px-4 py-3">Sumber Input</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($daftarPsn as $psn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($psn->nama_psn, 70) }}</td>
                                <td class="px-4 py-3">{{ $psn->klaster?->nama_klaster ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $psn->provinsi?->nama_provinsi ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $psn->statusPsn?->nama_status ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if ($psn->kategori_usulan)
                                        <span class="rounded-full text-xs px-2 py-1 {{ $psn->kategori_usulan === 'Carryover' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-700' }}">
                                            {{ $psn->kategori_usulan }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full text-xs px-2 py-1 {{ $psn->sumber_input === 'Manual' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $psn->sumber_input }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-3">
                                    <a href="{{ route('admin.psn.show', $psn) }}" class="text-blue-800 hover:underline">Lihat</a>
                                    @can('update', $psn)
                                        <a href="{{ route('admin.psn.edit', $psn) }}" class="text-blue-800 hover:underline">Ubah</a>
                                    @endcan
                                    @can('delete', $psn)
                                        <form method="POST" action="{{ route('admin.psn.destroy', $psn) }}" class="inline" onsubmit="return confirm('Hapus data PSN ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada data PSN.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarPsn->links() }}
        </div>
    </div>
</x-app-layout>
