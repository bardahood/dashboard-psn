<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Project Profile</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500">
                Rekap muatan Project Profile seluruh PSN, mengikuti struktur resmi paparan "Update Project Profile":
                bagian <strong>Perencanaan</strong> (Gambaran Umum, Indikator, Kontribusi Trisula, Penerima Manfaat,
                Register Risiko, RO/Proyek) dan <strong>Penjabaran Tahunan</strong> (Target Triwulanan/Bulanan tahun
                berjalan). Klik nama PSN untuk melihat Project Profile lengkap.
            </p>

            <form method="GET" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4 flex flex-wrap items-center gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama PSN..."
                       class="flex-1 min-w-[200px] rounded-lg border-gray-300 transition-colors text-sm">
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

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama PSN</th>
                            <th class="px-4 py-3">Klaster</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Provinsi</th>
                            <th class="px-4 py-3 text-center">RO/Proyek</th>
                            <th class="px-4 py-3 text-center">Risiko</th>
                            <th class="px-4 py-3 text-center" title="Jumlah komponen Perencanaan yang sudah terisi: Gambaran Umum, RO/Proyek, Risiko, Indikator, Trisula, Penerima Manfaat">Kelengkapan Perencanaan</th>
                            <th class="px-4 py-3 text-center" title="Jumlah komponen Penjabaran Tahunan {{ now()->year }} yang sudah terisi: Trisula TW, RO Bulanan/TW, Isu Lainnya, Evaluasi Status">Kelengkapan Penjabaran {{ now()->year }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @php
                            $badgeSkor = function (int $skor, int $total) {
                                $warna = $skor === $total ? 'bg-green-100 text-green-800' : ($skor === 0 ? 'bg-gray-100 text-gray-500' : 'bg-amber-100 text-amber-700');

                                return "<span class=\"inline-flex items-center rounded-full {$warna} px-2.5 py-0.5 text-xs font-medium\">{$skor}/{$total}</span>";
                            };
                        @endphp
                        @forelse ($daftarPsn as $psn)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.project-profile.show', $psn) }}" class="font-medium text-blue-800 hover:text-blue-900 hover:underline underline-offset-2">
                                        {{ \Illuminate\Support\Str::limit($psn->nama_psn, 60) }}
                                    </a>
                                    @if ($psn->nama_sub_proyek)
                                        <div class="text-xs text-gray-500">{{ $psn->nama_sub_proyek }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $psn->klaster?->nama_klaster ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $psn->statusPsn?->nama_status ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $psn->provinsi?->nama_provinsi ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $psn->ro_proyek_count }}</td>
                                <td class="px-4 py-3 text-center">{{ $psn->risiko_count }}</td>
                                <td class="px-4 py-3 text-center">{!! $badgeSkor($psn->skor_perencanaan, $totalKomponenPerencanaan) !!}</td>
                                <td class="px-4 py-3 text-center">{!! $badgeSkor($psn->skor_penjabaran, $totalKomponenPenjabaran) !!}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.project-profile.show', $psn) }}" class="text-blue-800 hover:text-blue-900 hover:underline underline-offset-2 text-sm font-medium">Lihat &rarr;</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $daftarPsn->links() }}
        </div>
    </div>
</x-app-layout>
