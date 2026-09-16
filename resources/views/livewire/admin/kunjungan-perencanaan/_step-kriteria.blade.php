@php
    $kriteriaKelompok = $kriteriaByKelompok->get($kelompok, collect())->groupBy('kode_kriteria');
@endphp

<div class="space-y-4">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-1">Kriteria {{ $kelompok }}</h3>
        <p class="text-xs text-gray-400">
            @if ($kelompok === 'Utama')
                Kriteria Utama bersifat PENGGUGUR -- jika ada jawaban "Tidak", rekomendasi keseluruhan otomatis menjadi "Ditolak".
            @else
                Skor 0-3 sesuai rubrik penilaian. Kriteria bertanda kondisional hanya tampil sesuai jenis pengusul/jenis proyek yang dipilih pada Data Usulan.
            @endif
        </p>
    </div>

    <form wire:submit="{{ $saveMethod }}" class="space-y-4">
        @foreach ($kriteriaKelompok as $kodeKriteria => $subButirList)
            @php $terlihat = $subButirList->filter(fn ($k) => $this->kriteriaTerlihat($k)); @endphp
            @if ($terlihat->isNotEmpty())
                <div class="bg-white shadow rounded-lg p-6">
                    <h4 class="font-medium text-gray-800 mb-3">{{ $kodeKriteria }}. {{ $terlihat->first()->judul_kriteria }}</h4>

                    <div class="space-y-4">
                        @foreach ($terlihat as $kriteria)
                            <div class="border-t pt-4 first:border-t-0 first:pt-0">
                                <div class="text-sm text-gray-700 mb-1">
                                    <span class="font-mono text-xs text-gray-400">{{ $kriteria->kode_kriteria }}{{ $kriteria->kode_sub }}</span>
                                    {{ $kriteria->sub_butir }}
                                </div>
                                @if ($kriteria->rubrik_penilaian)
                                    <p class="text-xs text-gray-400 mb-2">{{ $kriteria->rubrik_penilaian }}</p>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">
                                            {{ $kriteria->tipe_penilaian === 'YaTidak' ? 'Klaim Pengusul (Dokumen)' : 'Skor Desk Review PMO' }}
                                        </label>
                                        @if ($kriteria->tipe_penilaian === 'YaTidak')
                                            <input type="text" wire:model="kriteriaJawaban.{{ $kriteria->id }}.klaim_atau_temuan_dokumen" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        @else
                                            <select wire:model="kriteriaJawaban.{{ $kriteria->id }}.nilai_desk_review_pmo" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">-- Pilih --</option>
                                                @for ($i = 0; $i <= 3; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                            </select>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Temuan Lapangan</label>
                                        <input type="text" wire:model="kriteriaJawaban.{{ $kriteria->id }}.temuan_lapangan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Nilai Hasil Verifikasi</label>
                                        @if ($kriteria->tipe_penilaian === 'YaTidak')
                                            <select wire:model="kriteriaJawaban.{{ $kriteria->id }}.nilai_hasil_verifikasi" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">-- Pilih --</option>
                                                <option value="Ya">Ya</option>
                                                <option value="Tidak">Tidak</option>
                                            </select>
                                        @else
                                            <select wire:model="kriteriaJawaban.{{ $kriteria->id }}.nilai_hasil_verifikasi" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                                <option value="">-- Pilih --</option>
                                                @for ($i = 0; $i <= 3; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                            </select>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <input type="text" wire:model="kriteriaJawaban.{{ $kriteria->id }}.catatan" placeholder="Catatan (opsional)" class="block w-full rounded-md border-gray-300 shadow-sm text-xs">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        <div class="flex justify-between">
            <button type="button" wire:click="goToStep({{ $kelompok === 'Utama' ? 2 : ($kelompok === 'Pendukung' ? 3 : 4) }})" class="rounded-md border px-4 py-2 text-sm bg-white">&larr; Kembali</button>
            <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke {{ $nextLabel }} &rarr;
            </button>
        </div>
    </form>
</div>
