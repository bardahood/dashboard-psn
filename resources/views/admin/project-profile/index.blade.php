<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Project Profile</h2>
    </x-slot>

    @php
        $icons = [
            'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
            'eye' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
        ];

        $urutkan = function (string $kolom, string $label) use ($kolomUrut, $arahUrut) {
            $arahBaru = ($kolomUrut === $kolom && $arahUrut === 'asc') ? 'desc' : 'asc';
            $aktif = $kolomUrut === $kolom;

            return [
                'url' => request()->fullUrlWithQuery(['sort' => $kolom, 'direction' => $arahBaru, 'page' => null]),
                'label' => $label,
                'aktif' => $aktif,
                'arah' => $aktif ? $arahUrut : null,
            ];
        };
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Rekap muatan Project Profile seluruh PSN, mengikuti struktur resmi paparan "Update Project Profile":
                bagian <strong>Perencanaan</strong> (Gambaran Umum, Indikator, Kontribusi Trisula, Penerima Manfaat,
                Register Risiko, RO/Proyek) dan <strong>Penjabaran Tahunan</strong> (Target Triwulanan/Bulanan tahun
                berjalan). Klik nama PSN atau ikon dokumen untuk melihat Project Profile lengkap.
            </p>

            <form method="GET" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4 flex flex-wrap items-center gap-3">
                <select name="klaster_id" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">Semua Klaster</option>
                    @foreach ($klasterOptions as $k)
                        <option value="{{ $k->id }}" @selected((string) request('klaster_id') === (string) $k->id)>{{ $k->nama_klaster }}</option>
                    @endforeach
                </select>
                <select name="status_psn_id" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $s)
                        <option value="{{ $s->id }}" @selected((string) request('status_psn_id') === (string) $s->id)>{{ $s->nama_status }}</option>
                    @endforeach
                </select>
                <select name="provinsi_id" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">Semua Provinsi</option>
                    @foreach ($provinsiOptions as $p)
                        <option value="{{ $p->id }}" @selected((string) request('provinsi_id') === (string) $p->id)>{{ $p->nama_provinsi }}</option>
                    @endforeach
                </select>
                <select name="tipe_hierarki" class="rounded-lg border-gray-300 transition-colors text-sm">
                    <option value="">PKPN & PSN</option>
                    <option value="PKPN" @selected(request('tipe_hierarki') === 'PKPN')>PKPN</option>
                    <option value="PSN" @selected(request('tipe_hierarki') === 'PSN')>PSN</option>
                </select>
                <button class="rounded-lg bg-gray-700 text-white shadow-sm transition-all text-sm font-medium px-4 py-2 hover:bg-gray-600">Filter</button>
                @if (request()->anyFilled(['q', 'klaster_id', 'status_psn_id', 'provinsi_id', 'tipe_hierarki']))
                    <a href="{{ route('admin.project-profile.index') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
                @endif
            </form>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-hidden">
                <div class="p-4 flex flex-wrap items-center justify-between gap-3 border-b">
                    <h3 class="font-bold text-blue-800 text-lg tracking-wide">PROFILE PSN</h3>
                    <a href="{{ route('admin.project-profile.export', request()->query()) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-green-600 text-white shadow-sm hover:shadow hover:bg-green-700 transition-all px-4 py-2 text-sm font-medium">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5m-4.5 4.5V3" /></svg>
                        Download Xls
                    </a>
                </div>

                <form method="GET" class="px-4 pt-3 flex flex-wrap items-center justify-between gap-3 text-sm text-gray-600">
                    @foreach (request()->except(['per_page', 'q', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label class="flex items-center gap-2">
                        <select name="per_page" onchange="this.form.submit()" class="rounded-lg border-gray-300 transition-colors text-sm py-1">
                            @foreach ($pilihanPerHalaman as $opsi)
                                <option value="{{ $opsi }}" @selected($perHalaman === $opsi)>{{ $opsi }}</option>
                            @endforeach
                        </select>
                        entries per page
                    </label>
                    <label class="flex items-center gap-2">
                        Search:
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama PSN..."
                               class="rounded-lg border-gray-300 transition-colors text-sm py-1">
                    </label>
                </form>

                <div class="overflow-x-auto mt-3">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                @foreach ([$urutkan('kode_rkp', 'Kode PSN'), $urutkan('nama_psn', 'Nama PSN')] as $kol)
                                    <th class="px-4 py-3">
                                        <a href="{{ $kol['url'] }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                            {{ $kol['label'] }}
                                            <span class="text-gray-400">{{ $kol['aktif'] ? ($kol['arah'] === 'asc' ? '▲' : '▼') : '' }}</span>
                                        </a>
                                    </th>
                                @endforeach
                                <th class="px-4 py-3">Sub Proyek</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Klaster PSN</th>
                                <th class="px-4 py-3">Klaster PKPN</th>
                                <th class="px-4 py-3">Status PSN</th>
                                <th class="px-4 py-3">Pendanaan</th>
                                <th class="px-4 py-3">Pengusul</th>
                                <th class="px-4 py-3">Penanggung Jawab</th>
                                <th class="px-4 py-3">Pengelola</th>
                                <th class="px-4 py-3">Kontraktor</th>
                                <th class="px-4 py-3">Supervisi</th>
                                <th class="px-4 py-3">
                                    <a href="{{ $urutkan('tahun_penyelesaian', 'Tahun Selesai')['url'] }}" class="inline-flex items-center gap-1 hover:text-gray-700">
                                        Tahun Selesai
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($daftarPsn as $i => $psn)
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3">{{ $daftarPsn->firstItem() + $i }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $psn->kode_rkp ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.project-profile.show', $psn) }}" class="font-medium text-blue-800 hover:text-blue-900 hover:underline underline-offset-2">
                                            {{ \Illuminate\Support\Str::limit($psn->nama_psn, 60) }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">{{ $psn->nama_sub_proyek ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ collect([$psn->provinsi?->nama_provinsi, $psn->kabupaten_kota])->filter()->implode(' - ') ?: '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->klaster?->nama_klaster ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->tipe_hierarki ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->statusPsn?->nama_status ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->indikasi_sumber_pendanaan ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->pengusulInstansi?->nama_instansi ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->penanggungJawab->pluck('instansi.nama_instansi')->filter()->implode(', ') ?: '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->pengelolaInstansi?->nama_instansi ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->kontraktorInstansi?->nama_instansi ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->supervisiInstansi?->nama_instansi ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $psn->tahun_penyelesaian ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.project-profile.show', $psn) }}" title="Project Profile Lengkap"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-md bg-blue-700 text-white hover:bg-blue-800 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $icons['document'] !!}</svg>
                                        </a>
                                        <a href="{{ route('admin.psn.gambaran-umum', $psn) }}" title="Kelola/Ubah"
                                           class="inline-flex items-center justify-center h-7 w-7 rounded-md bg-amber-500 text-white hover:bg-amber-600 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $icons['eye'] !!}</svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="16" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4">
                    {{ $daftarPsn->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
