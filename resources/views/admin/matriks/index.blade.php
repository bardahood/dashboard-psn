<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Matriks Sandingan Sumber</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Sandingan ketersediaan PSN di 5 sumber: RKP Pemutakhiran 2026, Data PEKS3, Data PSI, Permenko,
                dan RKP 2027 (hasil pencocokan otomatis terhadap
                <a href="{{ route('admin.analisis-rkp2027') }}" class="text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">lampiran Daftar PSN RKP 2027</a>),
                serta kelengkapan 2 jenis data pendukung (Matrik Sandingan 17 Sept 2026): Data Gambaran Umum Proyek
                dan Data Project Profile Lengkap untuk kebutuhan evaluasi.
            </p>

            <form method="GET" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4 flex flex-wrap items-center gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama PSN..."
                       class="flex-1 min-w-[200px] rounded-lg border-gray-300 transition-colors text-sm">
                <select name="klaster" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">Semua Klaster</option>
                    @foreach ($klaster as $k)
                        <option value="{{ $k }}" @selected(request('klaster') === $k)>{{ $k }}</option>
                    @endforeach
                </select>
                <select name="provinsi" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">Semua Provinsi</option>
                    @foreach ($provinsi as $p)
                        <option value="{{ $p }}" @selected(request('provinsi') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="hanya_gap" value="1" @checked(request()->boolean('hanya_gap')) class="rounded border-gray-300">
                    Hanya tampilkan yang ada gap
                </label>
                <button class="rounded-lg bg-gray-700 text-white shadow-sm transition-all text-sm font-medium px-4 py-2 hover:bg-gray-600">Filter</button>
                @if (request()->anyFilled(['q', 'klaster', 'provinsi', 'hanya_gap']))
                    <a href="{{ route('admin.matriks-sandingan') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
                @endif
            </form>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama PSN</th>
                            <th class="px-4 py-3">Klaster</th>
                            <th class="px-4 py-3">Provinsi</th>
                            <th class="px-4 py-3 text-center">RKP 2026</th>
                            <th class="px-4 py-3 text-center">PEKS3</th>
                            <th class="px-4 py-3 text-center">PSI</th>
                            <th class="px-4 py-3 text-center">Permenko</th>
                            <th class="px-4 py-3 text-center">RKP 2027</th>
                            <th class="px-4 py-3 text-center">Gambaran Umum</th>
                            <th class="px-4 py-3 text-center">Project Profile Lengkap</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @php
                            $mark = fn ($v) => $v === null ? '<span class="text-gray-300">—</span>' : ($v ? '<span class="text-green-600 font-bold">&#10003;</span>' : '<span class="text-red-600 font-bold">&times;</span>');
                            $markStatus = fn (?string $v) => $v === null ? '<span class="text-gray-300">—</span>' : ($v === 'Ada' ? '<span class="text-green-600 font-bold">&#10003;</span>' : '<span class="text-red-600 font-bold">&times;</span>');
                        @endphp
                        @forelse ($matriks as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($row->nama_psn, 60) }}</td>
                                <td class="px-4 py-3">{{ $row->nama_klaster ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $row->nama_provinsi ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{!! $mark($row->rkp_pemutakhiran_2026) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $mark($row->data_peks3) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $mark($row->data_psi) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $mark($row->permenko) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $mark($row->rkp_2027) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $markStatus($row->ketersediaan_gambaran_umum) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $markStatus($row->ketersediaan_project_profile) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $matriks->links() }}
        </div>
    </div>
</x-app-layout>
