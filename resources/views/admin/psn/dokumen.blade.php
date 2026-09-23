<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil PSN: {{ \Illuminate\Support\Str::limit($psn->nama_psn, 60) }}
            </h2>
            <a href="{{ route('admin.psn.index') }}" class="text-sm text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">&larr; Kembali ke Data PSN</a>
        </div>
    </x-slot>

    @php
        // Diagram Kerangka Kelembagaan & Info Memo/Catatan Monev bersifat file/
        // dokumentasi pendukung, tidak masuk struktur "Struktur Project Profile"
        // (Gambaran Umum/Perencanaan/Trisula/Penjabaran) secara eksplisit --
        // dikumpulkan di tab ini bersama rekap Bukti Pelaporan RO supaya semua
        // berkas pendukung PSN ada di satu tempat.
        $buktiPelaporan = \App\Models\RoTargetPeriode::query()
            ->whereIn('ro_id', $psn->roProyek()->pluck('id'))
            ->whereNotNull('bukti_pelaporan_path')
            ->with('ro')
            ->orderByDesc('tahun')->orderByDesc('triwulan')->orderByDesc('bulan')
            ->get();
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.psn._profil-tabs')

            @if (session('status'))
                <div class="rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif

            @include('admin.psn._form-diagram')

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <h3 class="font-semibold text-gray-700 mb-3">Bukti Pelaporan RO/Proyek</h3>
                @if ($buktiPelaporan->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada bukti pelaporan yang diunggah. Unggah lewat tab Penjabaran saat menambah target periode RO.</p>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500"><tr><th class="py-2 pr-4">RO/Proyek</th><th class="py-2 pr-4">Periode</th><th class="py-2">Berkas</th></tr></thead>
                        <tbody class="divide-y">
                            @foreach ($buktiPelaporan as $p)
                                <tr>
                                    <td class="py-2 pr-4">{{ $p->ro->nama_ro }}</td>
                                    <td class="py-2 pr-4">{{ $p->tahun }} {{ $p->tipe_periode }}@if($p->triwulan) TW{{ $p->triwulan }}@endif@if($p->bulan) Bln{{ $p->bulan }}@endif</td>
                                    <td class="py-2"><a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->bukti_pelaporan_path) }}" target="_blank" class="text-blue-800 hover:underline">Lihat Berkas</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            @livewire('admin.sub-resource-manager', ['psn' => $psn, 'type' => 'info_memo'], key('info_memo'))

            @livewire('admin.sub-resource-manager', ['psn' => $psn, 'type' => 'catatan_monev'], key('catatan_monev'))
        </div>
    </div>
</x-app-layout>
