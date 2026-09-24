<div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
    <h3 class="font-semibold text-gray-700 mb-1">Spot-Check Dampak Trisula Pembangunan</h3>
    <p class="text-xs text-gray-400 mb-4">Verifikasi metodologi dampak, kondisi awal, target, model atribusi, dan peta penerima manfaat.</p>

    <form wire:submit="saveTrisula" class="space-y-4">
        @foreach ($dampakTrisulaList as $dampak)
            <div class="border rounded-md p-4">
                <div class="text-sm font-medium text-gray-700 mb-1">{{ $dampak->nama_dampak }}</div>
                @if ($dampak->contoh_indikator)
                    <p class="text-xs text-gray-400 mb-3">Contoh indikator: {{ $dampak->contoh_indikator }}</p>
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Indikator Klaim Dokumen</label>
                        <input type="text" wire:model="trisulaJawaban.{{ $dampak->id }}.indikator_klaim_dokumen" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Temuan Lapangan (Spot-check)</label>
                        <input type="text" wire:model="trisulaJawaban.{{ $dampak->id }}.temuan_lapangan_spotcheck" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kondisi Awal Terverifikasi?</label>
                        <select wire:model="trisulaJawaban.{{ $dampak->id }}.kondisi_awal_terverifikasi" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                            <option value="">-- Pilih --</option>
                            <option value="Ya">Ya</option>
                            <option value="Sebagian">Sebagian</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Atribusi Masuk Akal?</label>
                        <select wire:model="trisulaJawaban.{{ $dampak->id }}.atribusi_masuk_akal" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                            <option value="">-- Pilih --</option>
                            <option value="Ya">Ya</option>
                            <option value="Sebagian">Sebagian</option>
                            <option value="Tidak">Tidak</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                        <input type="text" wire:model="trisulaJawaban.{{ $dampak->id }}.catatan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-between">
            <button type="button" wire:click="goToStep(7)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
            <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Indeks Bukti &rarr;
            </button>
        </div>
    </form>
</div>
