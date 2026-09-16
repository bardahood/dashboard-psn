<div class="bg-white shadow rounded-lg p-6">
    <h3 class="font-semibold text-gray-700 mb-4">Bagian G: Hasil Evaluasi Tim Pengendalian</h3>

    <form wire:submit="saveEvaluasi" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Isu & Tantangan</label>
            <textarea wire:model="evaluasi.isu_tantangan" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kebutuhan Tindak Lanjut</label>
            <textarea wire:model="evaluasi.kebutuhan_tindak_lanjut" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hasil Evaluasi Proyek</label>
            <textarea wire:model="evaluasi.hasil_evaluasi_proyek" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
        </div>

        <div class="flex justify-between">
            <button type="button" wire:click="goToStep(6)" class="rounded-md border px-4 py-2 text-sm">&larr; Kembali</button>
            <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Bagian H &rarr;
            </button>
        </div>
    </form>
</div>
