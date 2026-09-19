<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ringkasan Debottlenecking</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-gray-500">
                Menyatukan register risiko, kebutuhan regulasi, dan isu/tindak lanjut hasil Kunjungan Pengendalian
                lintas PSN dalam satu ringkasan per klaster -- bahan identifikasi faktor penghambat dan rekomendasi
                percepatan (Bagian 2b-2c KAK Laporan Interim Pemantauan PSN).
            </p>

            <form method="GET" class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-4">
                <p class="text-sm font-medium text-gray-700 mb-2">
                    Fokus Klaster <span class="text-xs font-normal text-gray-400">(opsional -- kosongkan untuk semua klaster)</span>
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                    @foreach ($klasterOptions as $k)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="klaster_id[]" value="{{ $k->id }}"
                                   @checked(in_array($k->id, $klasterIds)) class="rounded border-gray-300">
                            {{ $k->nama_klaster }}
                        </label>
                    @endforeach
                </div>
                <button class="rounded-lg bg-gray-700 text-white shadow-sm transition-all text-sm font-medium px-4 py-2 hover:bg-gray-600">Filter</button>
                @if (count($klasterIds))
                    <a href="{{ route('admin.debottlenecking') }}" class="text-sm text-gray-500 ml-2 hover:underline">Reset</a>
                @endif
            </form>

            @php
                $levelBadge = fn ($level) => match ($level) {
                    'Sangat Tinggi' => 'bg-red-100 text-red-700',
                    'Tinggi' => 'bg-orange-100 text-orange-700',
                    'Sedang' => 'bg-amber-100 text-amber-700',
                    'Rendah' => 'bg-green-100 text-green-700',
                    default => 'bg-gray-100 text-gray-500',
                };
            @endphp

            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Register Risiko Aktif ({{ $risiko->count() }})</h3>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3">PSN</th>
                                <th class="px-4 py-3">Klaster</th>
                                <th class="px-4 py-3">Peristiwa Risiko</th>
                                <th class="px-4 py-3">Level Awal</th>
                                <th class="px-4 py-3">Level Residual Terkini</th>
                                <th class="px-4 py-3">Status Perlakuan Terkini</th>
                                <th class="px-4 py-3">PJ &amp; Target</th>
                                <th class="px-4 py-3">Rencana Perlakuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($risiko as $r)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($r->psn?->nama_psn, 40) }}</td>
                                    <td class="px-4 py-3">{{ $r->psn?->klaster?->nama_klaster ?? '-' }}</td>
                                    <td class="px-4 py-3 max-w-xs">
                                        {{ $r->peristiwa_risiko }}
                                        @if ($r->is_titik_kritis)
                                            <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-red-100 text-red-700 ml-1">Titik Kritis</span>
                                        @endif
                                        @if ($r->ro)
                                            <div class="text-xs text-gray-400 mt-0.5">RO: {{ $r->ro->nama_ro }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($r->level_risiko_awal)
                                            <span class="rounded-full text-xs px-2.5 py-1 font-medium {{ $levelBadge($r->level_risiko_awal) }}">{{ $r->level_risiko_awal }}</span>
                                        @else - @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($r->status_terkini?->risiko_residual_aktual)
                                            <span class="rounded-full text-xs px-2.5 py-1 font-medium {{ $levelBadge($r->status_terkini->risiko_residual_aktual) }}">{{ $r->status_terkini->risiko_residual_aktual }}</span>
                                        @else <span class="text-gray-400">Belum dilaporkan</span> @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $r->status_terkini?->status_perlakuan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $r->penanggungJawab?->nama_pic ?? '-' }}
                                        @if ($r->target_mulai || $r->target_selesai)
                                            <div class="text-gray-400">{{ $r->target_mulai?->format('d/m/y') ?? '-' }} &rarr; {{ $r->target_selesai?->format('d/m/y') ?? '-' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 max-w-xs">{{ $r->perlakuan_rencana ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">Belum ada data risiko.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Kebutuhan Regulasi ({{ $regulasi->count() }})</h3>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3">PSN</th>
                                <th class="px-4 py-3">Klaster</th>
                                <th class="px-4 py-3">Nama Regulasi</th>
                                <th class="px-4 py-3">Target Tahun</th>
                                <th class="px-4 py-3">Status Terkini</th>
                                <th class="px-4 py-3">Penanggung Jawab</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($regulasi as $r)
                                <tr class="align-top hover:bg-gray-50 {{ $r->terlambat ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($r->psn?->nama_psn, 40) }}</td>
                                    <td class="px-4 py-3">{{ $r->psn?->klaster?->nama_klaster ?? '-' }}</td>
                                    <td class="px-4 py-3 max-w-xs">{{ $r->nama_regulasi }}</td>
                                    <td class="px-4 py-3">
                                        {{ $r->target_tahun_penyelesaian ?? '-' }}
                                        @if ($r->terlambat)
                                            <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-red-100 text-red-700 ml-1">Terlambat</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $r->status_terkini ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $r->penanggungJawab?->nama_pic ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada kebutuhan regulasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-800 mb-2">Isu & Tindak Lanjut Terkini per PSN ({{ $isuTerkini->count() }})</h3>
                <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3">PSN</th>
                                <th class="px-4 py-3">Klaster</th>
                                <th class="px-4 py-3">Tanggal Kunjungan</th>
                                <th class="px-4 py-3">Isu & Tantangan</th>
                                <th class="px-4 py-3">Kebutuhan Tindak Lanjut</th>
                                <th class="px-4 py-3">Status Pengendalian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($isuTerkini as $k)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($k->psn?->nama_psn, 40) }}</td>
                                    <td class="px-4 py-3">{{ $k->psn?->klaster?->nama_klaster ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $k->tanggal_kunjungan?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 max-w-xs">{{ $k->isu_tantangan }}</td>
                                    <td class="px-4 py-3 max-w-xs">{{ $k->kebutuhan_tindak_lanjut ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($k->status_pengendalian)
                                            <span class="rounded-full text-xs px-2.5 py-1 font-medium bg-blue-100 text-blue-800">{{ $k->status_pengendalian }}</span>
                                        @else - @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada isu tercatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
