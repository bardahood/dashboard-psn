<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-md bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg px-2 py-2 overflow-x-auto">
        <div class="flex gap-1 min-w-max">
            @foreach ($steps as $num => $label)
                <button wire:click="goToStep({{ $num }})"
                        @if($num > 1 && !$kunjungan) disabled @endif
                        class="px-3 py-1.5 rounded-md text-sm whitespace-nowrap
                               {{ $step === $num ? 'bg-blue-800 text-white' : ($num > 1 && !$kunjungan ? 'text-gray-300 cursor-not-allowed' : 'text-gray-600 hover:bg-gray-100') }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        @error('step') <p class="px-3 pb-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    @if ($kunjungan && $gateUtamaGagal)
        <div class="rounded-md bg-red-50 text-red-700 px-4 py-3 text-sm">
            <strong>Kriteria Utama tidak terpenuhi.</strong> Rekomendasi otomatis akan diarahkan ke "Ditolak" apapun skor komponen lain.
        </div>
    @endif

    @if ($step === 1) @include('livewire.admin.kunjungan-perencanaan._step-usulan')
    @elseif ($step === 2) @include('livewire.admin.kunjungan-perencanaan._step-pmo')
    @elseif ($step === 3) @include('livewire.admin.kunjungan-perencanaan._step-kriteria', ['kelompok' => 'Utama', 'nextLabel' => 'Bagian Pendukung', 'saveMethod' => 'saveUtama'])
    @elseif ($step === 4) @include('livewire.admin.kunjungan-perencanaan._step-kriteria', ['kelompok' => 'Pendukung', 'nextLabel' => 'Bagian Kesiapan', 'saveMethod' => 'savePendukung'])
    @elseif ($step === 5) @include('livewire.admin.kunjungan-perencanaan._step-kriteria', ['kelompok' => 'Kesiapan', 'nextLabel' => 'Verifikasi Lokasi', 'saveMethod' => 'saveKesiapan'])
    @elseif ($step === 6) @include('livewire.admin.kunjungan-perencanaan._step-lokasi')
    @elseif ($step === 7) @include('livewire.admin.kunjungan-perencanaan._step-dokumen-teknis')
    @elseif ($step === 8) @include('livewire.admin.kunjungan-perencanaan._step-trisula')
    @elseif ($step === 9) @include('livewire.admin.kunjungan-perencanaan._step-bukti')
    @elseif ($step === 10) @include('livewire.admin.kunjungan-perencanaan._step-kesimpulan')
    @endif
</div>
