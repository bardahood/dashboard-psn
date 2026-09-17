<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail PSN</h2>
            @can('update', $psn)
                <a href="{{ route('admin.psn.edit', $psn) }}" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">Ubah</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.psn._profil-tabs')

            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-xl font-bold text-gray-900">{{ $psn->nama_psn }}</h1>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    @if ($psn->klaster) <span class="rounded-full bg-blue-100 text-blue-800 px-3 py-1">{{ $psn->klaster->nama_klaster }}</span> @endif
                    @if ($psn->statusPsn) <span class="rounded-full bg-green-100 text-green-800 px-3 py-1">{{ $psn->statusPsn->nama_status }}</span> @endif
                    @if ($psn->tipe_hierarki) <span class="rounded-full bg-gray-100 text-gray-700 px-3 py-1">{{ $psn->tipe_hierarki }}</span> @endif
                    <span class="rounded-full {{ $psn->sumber_input === 'Manual' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }} px-3 py-1">{{ $psn->sumber_input }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div><dt class="text-gray-500">Provinsi</dt><dd>{{ $psn->provinsi?->nama_provinsi ?? '-' }} @if($psn->kabupaten_kota), {{ $psn->kabupaten_kota }}@endif</dd></div>
                    <div><dt class="text-gray-500">Tahun Penyelesaian</dt><dd>{{ $psn->tahun_penyelesaian ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Tujuan Utama</dt><dd>{{ $psn->tujuan_utama ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Urgensi & Dasar Hukum</dt><dd>{{ $psn->urgensi ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Output Akhir</dt><dd>{{ $psn->output_akhir ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Nilai Investasi APBN</dt><dd>{{ $psn->nilai_investasi_apbn_rp ? 'Rp '.number_format($psn->nilai_investasi_apbn_rp, 0, ',', '.') : '-' }}</dd></div>
                    <div><dt class="text-gray-500">Nilai Investasi Non-APBN</dt><dd>{{ $psn->nilai_investasi_non_apbn_rp ? 'Rp '.number_format($psn->nilai_investasi_non_apbn_rp, 0, ',', '.') : '-' }}</dd></div>
                    <div><dt class="text-gray-500">Pengusul</dt><dd>{{ $psn->pengusulInstansi?->nama_instansi ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Pengelola</dt><dd>{{ $psn->pengelolaInstansi?->nama_instansi ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Kontraktor</dt><dd>{{ $psn->kontraktorInstansi?->nama_instansi ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Supervisi</dt><dd>{{ $psn->supervisiInstansi?->nama_instansi ?? '-' }}</dd></div>
                </dl>

                @if ($psn->diagram_kelembagaan_path)
                    <div class="mt-6 border-t pt-4">
                        <dt class="text-gray-500 text-sm mb-2">Visualisasi Kerangka Kelembagaan</dt>
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($psn->diagram_kelembagaan_path) }}" alt="Diagram Kerangka Kelembagaan {{ $psn->nama_psn }}" class="max-w-full rounded border">
                    </div>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="font-semibold text-gray-700 mb-3">RO/Proyek ({{ $psn->roProyek->count() }})</h2>
                @forelse ($psn->roProyek->whereNull('ro_induk_id') as $ro)
                    <div class="border-b py-2 text-sm flex justify-between">
                        <span>{{ $ro->nama_ro }} @if($ro->is_ro_kunci) <span class="text-xs text-red-600">(Kunci)</span> @endif</span>
                        <span class="text-gray-500">{{ $ro->tipe }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada data RO/Proyek.</p>
                @endforelse
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="font-semibold text-gray-700 mb-3">Register Risiko ({{ $psn->risiko->count() }})</h2>
                @forelse ($psn->risiko as $r)
                    <div class="border-b py-2 text-sm flex justify-between">
                        <span>{{ $r->peristiwa_risiko }}</span>
                        <span class="text-gray-500">{{ $r->level_risiko_awal ?? '-' }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada data risiko.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
