<div class="bg-white shadow rounded-lg p-6">
    <h3 class="font-semibold text-gray-700 mb-4">Ringkasan Hasil Penilaian PMO</h3>

    <form wire:submit="savePmo" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sesuai Program Prioritas RPJMN?</label>
            <select wire:model="pmo.hasil_pmo_rpjmn_program_prioritas" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih --</option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Termasuk PHTC?</label>
            <select wire:model="pmo.hasil_pmo_rpjmn_phtc" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih --</option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ditargetkan Selesai 2029?</label>
            <select wire:model="pmo.hasil_pmo_selesai_2029" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih --</option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Skor PMO Sementara</label>
            <input type="text" wire:model="pmo.skor_pmo_sementara" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Keputusan Saat Ini</label>
            <input type="text" wire:model="pmo.status_keputusan_saat_ini" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pembahasan PMO</label>
            <textarea wire:model="pmo.catatan_pembahasan_pmo" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
        </div>

        <div class="sm:col-span-2 flex justify-between">
            <button type="button" wire:click="goToStep(1)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
            <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Kriteria Utama &rarr;
            </button>
        </div>
    </form>
</div>
