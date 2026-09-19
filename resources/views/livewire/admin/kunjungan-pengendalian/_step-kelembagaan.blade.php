<div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
    <h3 class="font-semibold text-gray-700 mb-1">Bagian B: Kelembagaan & Stakeholder Mapping</h3>
    <p class="text-xs text-gray-400 mb-4">Bandingkan instansi tercatat pada profil PSN dengan temuan lapangan untuk 5 peran kelembagaan.</p>

    <form wire:submit="saveKelembagaan" class="space-y-4">
        @foreach ($peranList as $peran)
            <div class="border rounded-md p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-4 font-medium text-sm text-gray-700">{{ $peran }}</div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Instansi Tercatat</label>
                    <select wire:model="kelembagaan.{{ $peran }}.instansi_tercatat_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                        <option value="">-- Tidak Ada --</option>
                        @foreach ($instansiOptions as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Instansi Aktual (Temuan Lapangan)</label>
                    <input type="text" wire:model="kelembagaan.{{ $peran }}.instansi_aktual" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sesuai?</label>
                    <select wire:model="kelembagaan.{{ $peran }}.sesuai" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                        <option value="">-- Pilih --</option>
                        <option value="Ya">Ya</option>
                        <option value="Sebagian">Sebagian</option>
                        <option value="Tidak">Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                    <input type="text" wire:model="kelembagaan.{{ $peran }}.catatan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                </div>
            </div>
        @endforeach

        <div class="flex justify-between">
            <button type="button" wire:click="goToStep(1)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
            <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Bagian C &rarr;
            </button>
        </div>
    </form>
</div>
