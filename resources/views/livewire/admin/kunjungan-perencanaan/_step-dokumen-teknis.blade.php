<div class="bg-white shadow rounded-lg p-6">
    <h3 class="font-semibold text-gray-700 mb-4">Verifikasi Ketersediaan & Kemutakhiran Dokumen Teknis</h3>

    <form wire:submit="saveDokumenTeknis" class="space-y-4">
        @foreach ($dokumenTeknisList as $dokumen)
            <div class="border rounded-md p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-4 text-sm font-medium text-gray-700">{{ $dokumen->urutan }}. {{ $dokumen->nama_dokumen }}</div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tersedia?</label>
                    <select wire:model="dokumenTeknisJawaban.{{ $dokumen->id }}.tersedia" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">-- Pilih --</option>
                        <option value="Ya">Ya</option>
                        <option value="Sebagian">Sebagian</option>
                        <option value="Tidak">Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal/Versi Dokumen</label>
                    <input type="text" wire:model="dokumenTeknisJawaban.{{ $dokumen->id }}.tanggal_versi_dokumen" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kesesuaian Kondisi Lapangan</label>
                    <input type="text" wire:model="dokumenTeknisJawaban.{{ $dokumen->id }}.kesesuaian_kondisi_lapangan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                    <input type="text" wire:model="dokumenTeknisJawaban.{{ $dokumen->id }}.catatan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
            </div>
        @endforeach

        <div class="flex justify-between">
            <button type="button" wire:click="goToStep(6)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
            <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Dampak Trisula &rarr;
            </button>
        </div>
    </form>
</div>
