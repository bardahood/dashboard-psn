<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil PSN: {{ \Illuminate\Support\Str::limit($psn->nama_psn, 60) }}
            </h2>
            <a href="{{ route('admin.psn.index') }}" class="text-sm text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">&larr; Kembali ke Data PSN</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin.psn._profil-tabs')

            @livewire('admin.annual-target-manager', ['psn' => $psn, 'type' => 'indikator'], key('indikator'))

            @livewire('admin.annual-target-manager', ['psn' => $psn, 'type' => 'trisula', 'tampilan' => 'tahunan'], key('trisula-tahunan'))

            @livewire('admin.annual-target-manager', ['psn' => $psn, 'type' => 'penerima_manfaat'], key('penerima_manfaat'))

            @livewire('admin.risiko-manager', ['psn' => $psn])

            @livewire('admin.sub-resource-manager', ['psn' => $psn, 'type' => 'regulasi'], key('regulasi'))

            <div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-700">RO/Proyek/Non RO (Ringkasan Tahunan)</h3>
                    <a href="{{ route('admin.psn.penjabaran', $psn) }}" class="text-sm text-blue-700 font-medium hover:text-blue-900 hover:underline underline-offset-2 transition-colors">Kelola RO & Target Bulanan/Triwulanan &rarr;</a>
                </div>
                @php $roList = $psn->roProyek()->whereNull('ro_induk_id')->with('anak')->orderBy('id')->get(); @endphp
                @if ($roList->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data RO/Proyek. Tambahkan lewat tab Penjabaran.</p>
                @else
                    <div class="divide-y text-sm">
                        @foreach ($roList as $ro)
                            <div class="py-2 flex justify-between items-center">
                                <span>
                                    {{ $ro->nama_ro }}
                                    @if ($ro->is_ro_kunci) <span class="ml-1 text-xs rounded-full bg-red-100 text-red-700 px-2 py-0.5">RO Kunci</span> @endif
                                </span>
                                <span class="text-gray-500">{{ $ro->target_akhir ?? '-' }} {{ $ro->satuan }}</span>
                            </div>
                            @foreach ($ro->anak as $anak)
                                <div class="py-2 pl-4 flex justify-between items-center text-gray-600">
                                    <span>&#8618; {{ $anak->nama_ro }}</span>
                                    <span class="text-gray-500">{{ $anak->target_akhir ?? '-' }} {{ $anak->satuan }}</span>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>

            @include('admin.psn._next-button')
        </div>
    </div>
</x-app-layout>
