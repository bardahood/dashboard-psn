<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Skor & Rekomendasi Otomatis</h3>
        <div class="flex items-center gap-6">
            <div>
                <div class="text-3xl font-bold {{ $skorKeseluruhan >= 80 ? 'text-green-700' : ($skorKeseluruhan >= 50 ? 'text-amber-600' : 'text-red-700') }}">
                    {{ $skorKeseluruhan !== null ? $skorKeseluruhan : '-' }}
                </div>
                <div class="text-xs text-gray-500">Skor Keseluruhan (0-100)</div>
            </div>
            <div>
                <span class="rounded-full text-sm px-3 py-1 bg-gray-100 text-gray-700">{{ $rekomendasiOtomatis ?? 'Belum cukup data' }}</span>
                <div class="text-xs text-gray-400 mt-1">Saran otomatis dari kepatuhan pelaporan, kesesuaian fisik/anggaran, dan evaluasi risiko.</div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Bagian I: Kesimpulan & Pengesahan</h3>

        <form wire:submit="saveKesimpulan" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pengendalian</label>
                <select wire:model="kesimpulan.status_pengendalian" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Aktif Dikendalikan Sesuai Rencana">Aktif Dikendalikan Sesuai Rencana</option>
                    <option value="Perlu Perhatian">Perlu Perhatian</option>
                    <option value="Kritis/Perlu Eskalasi">Kritis/Perlu Eskalasi</option>
                    <option value="Selesai">Selesai</option>
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
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rekomendasi Kelanjutan Status</label>
                <textarea wire:model="kesimpulan.rekomendasi_kelanjutan_status" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kesimpulan Umum</label>
                <textarea wire:model="kesimpulan.kesimpulan_umum" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengesahan</label>
                <input type="date" wire:model="kesimpulan.tanggal_pengesahan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            </div>

            <div class="sm:col-span-2 flex justify-between">
                <button type="button" wire:click="goToStep(8)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
                <button type="submit" class="rounded-md bg-green-700 text-white px-4 py-2 text-sm hover:bg-green-600">
                    Simpan &amp; Sahkan Instrumen
                </button>
            </div>
        </form>
    </div>
</div>
