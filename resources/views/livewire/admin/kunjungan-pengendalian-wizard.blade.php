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
                    {{ chr(64 + $num) }}. {{ $label }}
                </button>
            @endforeach
        </div>
        @error('step') <p class="px-3 pb-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    @if ($psn)
        <div class="text-sm text-gray-500">PSN: <span class="font-medium text-gray-800">{{ $psn->nama_psn }}</span></div>
    @endif

    @if ($step === 1) @include('livewire.admin.kunjungan-pengendalian._step-identitas')
    @elseif ($step === 2) @include('livewire.admin.kunjungan-pengendalian._step-kelembagaan')
    @elseif ($step === 3) @include('livewire.admin.kunjungan-pengendalian._step-fisik')
    @elseif ($step === 4) @include('livewire.admin.kunjungan-pengendalian._step-anggaran')
    @elseif ($step === 5) @include('livewire.admin.kunjungan-pengendalian._step-risiko')
    @elseif ($step === 6) @include('livewire.admin.kunjungan-pengendalian._step-regulasi')
    @elseif ($step === 7) @include('livewire.admin.kunjungan-pengendalian._step-evaluasi')
    @elseif ($step === 8) @include('livewire.admin.kunjungan-pengendalian._step-dokumentasi')
    @elseif ($step === 9) @include('livewire.admin.kunjungan-pengendalian._step-kesimpulan')
    @endif
</div>
