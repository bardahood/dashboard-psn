<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Skor & Rekomendasi Otomatis</h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
            <div class="text-center">
                <div class="text-xl font-bold text-gray-700">{{ $skorPendukung ?? '-' }}</div>
                <div class="text-xs text-gray-500">Pendukung (35%)</div>
            </div>
            <div class="text-center">
                <div class="text-xl font-bold text-gray-700">{{ $skorKesiapan ?? '-' }}</div>
                <div class="text-xs text-gray-500">Kesiapan (35%)</div>
            </div>
            <div class="text-center">
                <div class="text-xl font-bold text-gray-700">{{ $skorLokasi ?? '-' }}</div>
                <div class="text-xs text-gray-500">Lokasi (15%)</div>
            </div>
            <div class="text-center">
                <div class="text-xl font-bold text-gray-700">{{ $skorTrisula ?? '-' }}</div>
                <div class="text-xs text-gray-500">Trisula (15%)</div>
            </div>
        </div>

        <div class="flex items-center gap-6 border-t pt-4">
            <div>
                <div class="text-3xl font-bold {{ $gateUtamaGagal ? 'text-red-700' : ($skorKeseluruhan >= 80 ? 'text-green-700' : ($skorKeseluruhan >= 50 ? 'text-amber-600' : 'text-red-700')) }}">
                    {{ $gateUtamaGagal ? '—' : ($skorKeseluruhan !== null ? $skorKeseluruhan : '-') }}
                </div>
                <div class="text-xs text-gray-500">Skor Keseluruhan (0-100)</div>
            </div>
            <div>
                <span class="rounded-full text-sm px-3 py-1 {{ $gateUtamaGagal ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $rekomendasiOtomatis ?? 'Belum cukup data' }}
                </span>
                @if ($gateUtamaGagal)
                    <div class="text-xs text-red-600 mt-1">Ada Kriteria Utama bernilai "Tidak" -- rekomendasi otomatis digugurkan menjadi "Ditolak".</div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Kesimpulan & Rekomendasi</h3>

        <form wire:submit="saveKesimpulan" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fokus Verifikasi Lapangan</label>
                <textarea wire:model="kesimpulan.fokus_verifikasi_lapangan" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pihak Pengusul Ditemui</label>
                <input type="text" wire:model="kesimpulan.pihak_pengusul_ditemui" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Narasumber Teknis Lain</label>
                <input type="text" wire:model="kesimpulan.narasumber_teknis_lain" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dasar Justifikasi</label>
                <textarea wire:model="kesimpulan.dasar_justifikasi" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dokumen yang Masih Diperlukan</label>
                <textarea wire:model="kesimpulan.dokumen_masih_diperlukan" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu Pemenuhan</label>
                <input type="date" wire:model="kesimpulan.batas_waktu_pemenuhan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Diverifikasi Oleh</label>
                <select wire:model="kesimpulan.diverifikasi_oleh_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih PIC --</option>
                    @foreach ($picOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mengetahui</label>
                <select wire:model="kesimpulan.mengetahui_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih PIC --</option>
                    @foreach ($picOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rekomendasi Keseluruhan</label>
                <select wire:model="kesimpulan.rekomendasi_keseluruhan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Layak Dilanjutkan">Layak Dilanjutkan</option>
                    <option value="Layak dengan Catatan">Layak dengan Catatan</option>
                    <option value="Perlu Perbaikan Dokumen">Perlu Perbaikan Dokumen</option>
                    <option value="Belum Layak">Belum Layak</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex justify-between">
                <button type="button" wire:click="goToStep(9)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
                <button type="submit" class="rounded-md bg-green-700 text-white px-4 py-2 text-sm hover:bg-green-600">
                    Simpan &amp; Sahkan Instrumen
                </button>
            </div>
        </form>
    </div>
</div>
