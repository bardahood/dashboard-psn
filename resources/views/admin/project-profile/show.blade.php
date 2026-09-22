<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Project Profile</h2>
            <a href="{{ route('admin.psn.show', $psn) }}" class="text-sm text-blue-800 hover:text-blue-900 hover:underline underline-offset-2">Buka mode edit &rarr;</a>
        </div>
    </x-slot>

    @php
        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $namaTw = [1 => 'TW I', 2 => 'TW II', 3 => 'TW III', 4 => 'TW IV'];
        $labelTrisula = [
            'Pertumbuhan Ekonomi' => 'Pertumbuhan Ekonomi Berkualitas (Investasi)',
            'Kemiskinan' => 'Penurunan Kemiskinan dan Ketimpangan (Serapan Tenaga Kerja)',
            'Sumber Daya Manusia' => 'Peningkatan Kualitas Sumber Daya Manusia (Indeks Modal Manusia)',
        ];
        // Desimal maksimal 2 (mengikuti presisi decimal:2 kolom target/realisasi di DB),
        // trailing zero dipotong supaya angka bulat (mis. Rupiah) tetap tampil rapi
        // tanpa kehilangan presisi nilai pecahan (mis. target RO 0,25).
        $angka = function ($v, int $desimalMaks = 2) {
            if ($v === null) {
                return '-';
            }
            $formatted = number_format((float) $v, $desimalMaks, ',', '.');

            return $desimalMaks > 0 ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
        };
        $terbaruDari = fn ($koleksi, string $field) => optional(
            $koleksi->sortByDesc('tahun')->first(fn ($r) => $r->{$field} !== null)
        )->{$field};
        $targetAkhirDari = fn ($koleksi) => $koleksi->pluck('target_akhir')->filter(fn ($v) => $v !== null)->first();

        $roSemuaFlat = $roIndukList->flatMap(fn ($ro) => collect([$ro])->merge($ro->anak));
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $psn->nama_psn }}</h1>
                        @if ($psn->nama_sub_proyek)
                            <p class="text-sm text-gray-500 mt-0.5">Sub Proyek: {{ $psn->nama_sub_proyek }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            @if ($psn->klaster) <span class="rounded-full bg-blue-100 text-blue-800 px-3 py-1">Klaster {{ $psn->klaster->nama_klaster }}</span> @endif
                            @if ($psn->tipe_hierarki) <span class="rounded-full bg-gray-100 text-gray-700 px-3 py-1">{{ $psn->tipe_hierarki }}</span> @endif
                            @if ($psn->statusPsn) <span class="rounded-full bg-green-100 text-green-800 px-3 py-1">{{ $psn->statusPsn->nama_status }}</span> @endif
                            <span class="rounded-full {{ $psn->kategori_usulan === 'Carryover' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }} px-3 py-1">{{ $psn->kategori_usulan ?? 'Kategori usulan belum diisi' }}</span>
                        </div>
                    </div>
                    <nav class="flex gap-2 text-sm">
                        <a href="#perencanaan" class="rounded-lg bg-blue-50 text-blue-800 px-3 py-1.5 font-medium hover:bg-blue-100">Perencanaan</a>
                        <a href="#penjabaran" class="rounded-lg bg-blue-50 text-blue-800 px-3 py-1.5 font-medium hover:bg-blue-100">Penjabaran Tahunan</a>
                    </nav>
                </div>
            </div>

            {{-- =========================== PERENCANAAN =========================== --}}
            <h2 id="perencanaan" class="scroll-mt-20 text-lg font-bold text-gray-800 pt-2">Perencanaan</h2>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Gambaran Umum</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div><dt class="text-gray-500">Klaster PKPN/PSN</dt><dd>{{ $psn->klaster?->nama_klaster ?? '-' }} ({{ $psn->tipe_hierarki ?? '-' }})</dd></div>
                    <div><dt class="text-gray-500">Status PSN</dt><dd>{{ $psn->statusPsn?->nama_status ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Diagram Kerangka Kerja Logis (Kode RKP)</dt><dd>{{ $psn->kode_rkp ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Lokasi</dt><dd>{{ $psn->provinsi?->nama_provinsi ?? '-' }}{{ $psn->kabupaten_kota ? ', '.$psn->kabupaten_kota : '' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Tujuan Utama</dt><dd>{{ $psn->tujuan_utama ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Urgensi & Dasar Hukum</dt><dd>{{ $psn->urgensi ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Tahun & Output Akhir</dt><dd>{{ $psn->bulan_penyelesaian ? $namaBulan[$psn->bulan_penyelesaian].' ' : '' }}{{ $psn->tahun_penyelesaian ?? '-' }} &mdash; {{ $psn->output_akhir ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Data Teknis</dt><dd>{{ $psn->data_teknis ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Nilai Investasi/Anggaran Total (s.d akhir proyek)</dt><dd>APBN: {{ $psn->nilai_investasi_apbn_rp ? 'Rp '.$angka($psn->nilai_investasi_apbn_rp) : '-' }} &middot; Non-APBN: {{ $psn->nilai_investasi_non_apbn_rp ? 'Rp '.$angka($psn->nilai_investasi_non_apbn_rp) : '-' }}</dd></div>
                    <div><dt class="text-gray-500">Pengusul / Penanggung Jawab</dt><dd>{{ $psn->pengusulInstansi?->nama_instansi ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Pengelola / Kontraktor / Supervisi</dt><dd>{{ $psn->pengelolaInstansi?->nama_instansi ?? '-' }} / {{ $psn->kontraktorInstansi?->nama_instansi ?? '-' }} / {{ $psn->supervisiInstansi?->nama_instansi ?? '-' }}</dd></div>
                </dl>

                @if ($psn->diagram_kelembagaan_path)
                    <div class="mt-6 border-t pt-4">
                        <dt class="text-gray-500 text-sm mb-2">Visualisasi Kerangka Kelembagaan</dt>
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($psn->diagram_kelembagaan_path) }}" alt="Diagram Kerangka Kelembagaan {{ $psn->nama_psn }}" class="max-w-full rounded border">
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Dasar Hukum</h3>
                @if ($psn->dasarHukum->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-2 pr-4">Nama Regulasi</th><th class="py-2 pr-4">Nomor</th><th class="py-2 pr-4">Tahun</th><th class="py-2">Keterangan</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($psn->dasarHukum as $dh)
                                <tr><td class="py-2 pr-4">{{ $dh->nama_regulasi }}</td><td class="py-2 pr-4">{{ $dh->nomor_regulasi ?? '-' }}</td><td class="py-2 pr-4">{{ $dh->tahun ?? '-' }}</td><td class="py-2">{{ $dh->keterangan ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Stakeholder Mapping & Kerangka Kelembagaan</h3>
                @if ($psn->stakeholder->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-2 pr-4">Aktor/Instansi</th><th class="py-2 pr-4">Jenis Aktor</th><th class="py-2 pr-4">Pengelompokan Fungsi</th><th class="py-2">Peran/Fungsi</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($psn->stakeholder as $sh)
                                <tr><td class="py-2 pr-4">{{ $sh->nama_pemangku_kepentingan }}</td><td class="py-2 pr-4">{{ $sh->kategori_aktor }}</td><td class="py-2 pr-4">{{ ['Kebijakan/Regulasi/Pengarah', 'Fasilitator Wilayah', 'Operator/Investor/Off-taker', 'Partisipan/Penerima Manfaat/Riset'][$sh->level_kelembagaan - 1] ?? '-' }}</td><td class="py-2">{{ $sh->peran_deskripsi ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-700 mb-3">Indikator Output/Outcome (Target Tahunan)</h3>
                @if ($psn->indikator->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <table class="min-w-full text-sm whitespace-nowrap">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Indikator</th><th class="py-2 pr-4">Satuan</th><th class="py-2 pr-4">Baseline</th><th class="py-2 pr-4">Target Akhir</th>
                                @foreach ($tahunGrid as $tahun) <th class="py-2 pr-4 text-center" colspan="2">{{ $tahun }}</th> @endforeach
                                <th class="py-2 pr-4 text-center">% thd Akhir</th><th class="py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($psn->indikator as $ind)
                                @php $tahunan = $ind->targetTahunan->keyBy('tahun'); @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $ind->nama_indikator }}</td>
                                    <td class="py-2 pr-4">{{ $ind->satuan ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $ind->baseline ?? '-' }}{{ $ind->baseline_tahun ? ' ('.$ind->baseline_tahun.')' : '' }}</td>
                                    <td class="py-2 pr-4">{{ $targetAkhirDari($ind->targetTahunan) ?? '-' }}</td>
                                    @foreach ($tahunGrid as $tahun)
                                        <td class="py-2 pr-2 text-center text-gray-400">{{ $angka($tahunan->get($tahun)?->target) }}</td>
                                        <td class="py-2 pr-4 text-center">{{ $angka($tahunan->get($tahun)?->realisasi) }}</td>
                                    @endforeach
                                    <td class="py-2 pr-4 text-center">{{ $terbaruDari($ind->targetTahunan, 'persen_terhadap_target_akhir') !== null ? $angka($terbaruDari($ind->targetTahunan, 'persen_terhadap_target_akhir'), 1).'%' : '-' }}</td>
                                    <td class="py-2 text-center">{{ $terbaruDari($ind->targetTahunan, 'status_capaian') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="text-xs text-gray-400 mt-2">Kolom abu-abu = Target, kolom di sampingnya = Realisasi.</p>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-700 mb-3">Kontribusi Terhadap Trisula Pembangunan (Target Tahunan)</h3>
                @if ($psn->trisulaKontribusi->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <table class="min-w-full text-sm whitespace-nowrap">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Trisula</th><th class="py-2 pr-4">Indikator</th><th class="py-2 pr-4">Satuan</th><th class="py-2 pr-4">Baseline</th>
                                @foreach ($tahunGrid as $tahun) <th class="py-2 pr-4 text-center" colspan="2">{{ $tahun }}</th> @endforeach
                                <th class="py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($psn->trisulaKontribusi as $tk)
                                @php $tahunan = $tk->targetPeriode->where('tipe_periode', 'TAHUNAN')->keyBy('tahun'); @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-normal max-w-[10rem]">{{ $labelTrisula[$tk->kategori_trisula] ?? $tk->kategori_trisula }}{{ $tk->sub_kategori_sdm ? ' — '.$tk->sub_kategori_sdm : '' }}</td>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $tk->nama_indikator }}</td>
                                    <td class="py-2 pr-4">{{ $tk->satuan ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $tk->baseline ?? '-' }}</td>
                                    @foreach ($tahunGrid as $tahun)
                                        <td class="py-2 pr-2 text-center text-gray-400">{{ $angka($tahunan->get($tahun)?->target) }}</td>
                                        <td class="py-2 pr-4 text-center">{{ $angka($tahunan->get($tahun)?->realisasi) }}</td>
                                    @endforeach
                                    <td class="py-2 text-center">{{ $terbaruDari($tahunan, 'status_capaian') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-700 mb-3">Penerima Manfaat (Target Tahunan)</h3>
                @if ($psn->penerimaManfaat->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <table class="min-w-full text-sm whitespace-nowrap">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Kategori Penerima</th><th class="py-2 pr-4">Satuan</th><th class="py-2 pr-4">Baseline</th>
                                @foreach ($tahunGrid as $tahun) <th class="py-2 pr-4 text-center" colspan="2">{{ $tahun }}</th> @endforeach
                                <th class="py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($psn->penerimaManfaat as $pm)
                                @php $tahunan = $pm->targetTahunan->keyBy('tahun'); @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $pm->kategori_penerima }}</td>
                                    <td class="py-2 pr-4">{{ $pm->satuan ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $pm->baseline ?? '-' }}</td>
                                    @foreach ($tahunGrid as $tahun)
                                        <td class="py-2 pr-2 text-center text-gray-400">{{ $angka($tahunan->get($tahun)?->target) }}</td>
                                        <td class="py-2 pr-4 text-center">{{ $angka($tahunan->get($tahun)?->realisasi) }}</td>
                                    @endforeach
                                    <td class="py-2 text-center">{{ $terbaruDari($pm->targetTahunan, 'status_capaian') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Register Risiko</h3>
                @if ($psn->risiko->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr><th class="py-2 pr-4">Peristiwa Risiko</th><th class="py-2 pr-4">Kategori</th><th class="py-2 pr-4">Level</th><th class="py-2 pr-4">Perlakuan/Rencana</th><th class="py-2 pr-4">PJ Risiko</th><th class="py-2 pr-4">PJ Perlakuan</th><th class="py-2 pr-4">Terkait RO</th><th class="py-2">Progres Terkini</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($psn->risiko as $r)
                                @php $statusTerkini = $r->statusPeriode->sortByDesc('tahun')->sortByDesc('triwulan')->first(); @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $r->peristiwa_risiko }}</td>
                                    <td class="py-2 pr-4">{{ $r->kategori_risiko ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r->level_risiko_awal ?? '-' }}</td>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $r->perlakuan_rencana ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r->penanggungJawab?->nama_pic ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r->pelaksanaPerlakuan?->nama_pic ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $r->ro?->nama_ro ?? '-' }} @if($r->is_titik_kritis)<span class="ml-1 rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs">Titik Kritis</span>@endif</td>
                                    <td class="py-2">{{ $statusTerkini ? "TW{$statusTerkini->triwulan}/{$statusTerkini->tahun}: {$statusTerkini->progres_pelaksanaan_persen}% ({$statusTerkini->status_perlakuan})" : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Kebutuhan Regulasi</h3>
                @if ($psn->kebutuhanRegulasi->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-2 pr-4">Nama Regulasi</th><th class="py-2 pr-4">Justifikasi Kebutuhan</th><th class="py-2 pr-4">Target Tahun</th><th class="py-2">Penanggung Jawab</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($psn->kebutuhanRegulasi as $kr)
                                <tr><td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $kr->nama_regulasi }}</td><td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $kr->justifikasi_kebutuhan ?? '-' }}</td><td class="py-2 pr-4">{{ $kr->target_tahun_penyelesaian ?? '-' }}</td><td class="py-2">{{ $kr->penanggungJawab?->nama_pic ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-700 mb-3">RO/Proyek/Non RO (Ringkasan Tahunan)</h3>
                @if ($roIndukList->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <table class="min-w-full text-sm whitespace-nowrap">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">RO/Proyek/Aktivitas</th><th class="py-2 pr-4">Satuan</th><th class="py-2 pr-4">Target Akhir</th><th class="py-2 pr-4">Lokasi</th><th class="py-2 pr-4">Instansi Pelaksana</th><th class="py-2 pr-4 text-center">Kunci</th>
                                @foreach ($tahunGrid as $tahun) <th class="py-2 pr-4 text-center" colspan="2">{{ $tahun }}</th> @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($roIndukList as $ro)
                                @php $tahunan = $ro->targetPeriode->where('tipe_periode', 'TAHUNAN')->keyBy('tahun'); @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs font-medium">{{ $ro->nama_ro }} <span class="text-xs text-gray-400">({{ $ro->tipe }})</span></td>
                                    <td class="py-2 pr-4">{{ $ro->satuan ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $ro->target_akhir ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $ro->lokasi ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $ro->instansiPelaksana?->nama_instansi ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-center">{{ $ro->is_ro_kunci ? '✓' : '-' }}</td>
                                    @foreach ($tahunGrid as $tahun)
                                        <td class="py-2 pr-2 text-center text-gray-400">{{ $angka($tahunan->get($tahun)?->target) }}</td>
                                        <td class="py-2 pr-4 text-center">{{ $angka($tahunan->get($tahun)?->realisasi_fisik) }}</td>
                                    @endforeach
                                </tr>
                                @foreach ($ro->anak as $anak)
                                    @php $tahunanAnak = $anak->targetPeriode->where('tipe_periode', 'TAHUNAN')->keyBy('tahun'); @endphp
                                    <tr class="bg-gray-50/50">
                                        <td class="py-2 pr-4 pl-6 whitespace-normal max-w-xs">&#8618; {{ $anak->nama_ro }} <span class="text-xs text-gray-400">({{ $anak->tipe }})</span></td>
                                        <td class="py-2 pr-4">{{ $anak->satuan ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $anak->target_akhir ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $anak->lokasi ?? '-' }}</td>
                                        <td class="py-2 pr-4">{{ $anak->instansiPelaksana?->nama_instansi ?? '-' }}</td>
                                        <td class="py-2 pr-4 text-center">{{ $anak->is_ro_kunci ? '✓' : '-' }}</td>
                                        @foreach ($tahunGrid as $tahun)
                                            <td class="py-2 pr-2 text-center text-gray-400">{{ $angka($tahunanAnak->get($tahun)?->target) }}</td>
                                            <td class="py-2 pr-4 text-center">{{ $angka($tahunanAnak->get($tahun)?->realisasi_fisik) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- ======================= PENJABARAN TAHUNAN ======================== --}}
            <div class="flex items-center justify-between pt-2">
                <h2 id="penjabaran" class="scroll-mt-20 text-lg font-bold text-gray-800">Penjabaran Tahunan</h2>
                <form method="GET" action="{{ route('admin.project-profile.show', $psn) }}#penjabaran" class="flex items-center gap-2 text-sm">
                    <label for="tahun" class="text-gray-500">Tahun:</label>
                    <select name="tahun" id="tahun" onchange="this.form.submit()" class="rounded-lg border-gray-300 transition-colors text-sm">
                        @foreach ($tahunOptions as $tahun)
                            <option value="{{ $tahun }}" @selected($tahun === $tahunPenjabaran)>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6 overflow-x-auto">
                <h3 class="font-semibold text-gray-700 mb-3">Kontribusi Terhadap Trisula Pembangunan (Target TW {{ $tahunPenjabaran }})</h3>
                @if ($psn->trisulaKontribusi->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <table class="min-w-full text-sm whitespace-nowrap">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Trisula</th><th class="py-2 pr-4">Indikator</th>
                                @foreach ($namaTw as $tw) <th class="py-2 pr-4 text-center" colspan="2">{{ $tw }}</th> @endforeach
                                <th class="py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($psn->trisulaKontribusi as $tk)
                                @php $tw = $tk->targetPeriode->where('tipe_periode', 'TRIWULANAN')->where('tahun', $tahunPenjabaran)->keyBy('triwulan'); @endphp
                                <tr>
                                    <td class="py-2 pr-4 whitespace-normal max-w-[10rem]">{{ $labelTrisula[$tk->kategori_trisula] ?? $tk->kategori_trisula }}</td>
                                    <td class="py-2 pr-4 whitespace-normal max-w-xs">{{ $tk->nama_indikator }}</td>
                                    @foreach (array_keys($namaTw) as $i)
                                        <td class="py-2 pr-2 text-center text-gray-400">{{ $angka($tw->get($i)?->target) }}</td>
                                        <td class="py-2 pr-4 text-center">{{ $angka($tw->get($i)?->realisasi) }}</td>
                                    @endforeach
                                    <td class="py-2 text-center">{{ $terbaruDari($tw, 'status_capaian') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">RO/Proyek/Aktivitas & Critical Path (Target Bulanan/Triwulanan {{ $tahunPenjabaran }})</h3>
                @if ($roSemuaFlat->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($roSemuaFlat as $item)
                            @php
                                $periodeTahunIni = $item->targetPeriode->where('tahun', $tahunPenjabaran)->whereIn('tipe_periode', ['BULANAN', 'TRIWULANAN'])
                                    ->sortBy([['triwulan', 'asc'], ['bulan', 'asc']]);
                                $isCriticalPath = $item->is_ro_kunci || $psn->risiko->where('ro_id', $item->id)->where('is_titik_kritis', true)->isNotEmpty();
                            @endphp
                            <div class="border rounded-lg p-4">
                                <div class="flex flex-wrap items-center gap-2 text-sm font-medium text-gray-800">
                                    {{ $item->nama_ro }}
                                    <span class="text-xs font-normal text-gray-400">({{ $item->tipe }} &middot; {{ $item->satuan ?? 'satuan belum diisi' }} &middot; {{ $item->lokasi ?? 'lokasi belum diisi' }} &middot; {{ $item->instansiPelaksana?->nama_instansi ?? 'instansi belum diisi' }})</span>
                                    @if ($isCriticalPath)
                                        <span class="rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs">Critical Path</span>
                                    @endif
                                </div>
                                @if ($periodeTahunIni->isEmpty())
                                    <p class="text-sm text-gray-400 mt-2">Belum ada data periode tahun {{ $tahunPenjabaran }}.</p>
                                @else
                                    <div class="overflow-x-auto mt-2">
                                        <table class="min-w-full text-sm whitespace-nowrap">
                                            <thead class="text-left text-gray-500">
                                                <tr><th class="py-1.5 pr-4">Periode</th><th class="py-1.5 pr-4">Target</th><th class="py-1.5 pr-4">Realisasi Fisik</th><th class="py-1.5 pr-4">Realisasi Anggaran (juta Rp)</th><th class="py-1.5 pr-4">Sumber Dana</th><th class="py-1.5 pr-4">Permasalahan</th><th class="py-1.5 pr-4">Kebutuhan Dukungan</th><th class="py-1.5">Bukti</th></tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                @foreach ($periodeTahunIni as $p)
                                                    <tr>
                                                        <td class="py-1.5 pr-4">{{ $p->tipe_periode === 'BULANAN' ? $namaBulan[$p->bulan] : ($namaTw[$p->triwulan] ?? '-') }}</td>
                                                        <td class="py-1.5 pr-4">{{ $angka($p->target) }}</td>
                                                        <td class="py-1.5 pr-4">{{ $angka($p->realisasi_fisik) }}</td>
                                                        <td class="py-1.5 pr-4">{{ $angka($p->realisasi_anggaran_juta_rp) }}</td>
                                                        <td class="py-1.5 pr-4">{{ $p->indikasi_sumber_pendanaan ?? '-' }}</td>
                                                        <td class="py-1.5 pr-4 whitespace-normal max-w-xs">{{ $p->permasalahan ?? '-' }}</td>
                                                        <td class="py-1.5 pr-4 whitespace-normal max-w-xs">{{ $p->kebutuhan_dukungan ?? '-' }}</td>
                                                        <td class="py-1.5">
                                                            @if ($p->bukti_pelaporan_path)
                                                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->bukti_pelaporan_path) }}" target="_blank" class="text-blue-800 hover:underline">Lihat</a>
                                                            @else - @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Deskripsi / Isu Lainnya dan Kebutuhan Dukungan</h3>
                @if ($psn->isuLainnya->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-2 pr-4">No</th><th class="py-2 pr-4">Deskripsi Isu</th><th class="py-2">Kebutuhan Dukungan</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($psn->isuLainnya->sortBy('nomor') as $isu)
                                <tr><td class="py-2 pr-4">{{ $isu->nomor ?? '-' }}</td><td class="py-2 pr-4 whitespace-normal max-w-md">{{ $isu->deskripsi_isu }}</td><td class="py-2 whitespace-normal max-w-md">{{ $isu->kebutuhan_dukungan ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Kebutuhan Status PSN Tahun Selanjutnya / Justifikasi Kebutuhan</h3>
                @if ($psn->evaluasiStatus->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-2 pr-4">Tahun Evaluasi</th><th class="py-2 pr-4">Masih Butuh Status PSN?</th><th class="py-2">Justifikasi</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($psn->evaluasiStatus->sortByDesc('tahun_evaluasi') as $ev)
                                <tr class="{{ $ev->tahun_evaluasi === $tahunPenjabaran ? 'bg-blue-50/60' : '' }}">
                                    <td class="py-2 pr-4">{{ $ev->tahun_evaluasi }}</td>
                                    <td class="py-2 pr-4">{{ $ev->masih_butuh_status_psn === null ? '-' : ($ev->masih_butuh_status_psn ? 'Ya' : 'Tidak') }}</td>
                                    <td class="py-2 whitespace-normal max-w-md">{{ $ev->justifikasi ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
