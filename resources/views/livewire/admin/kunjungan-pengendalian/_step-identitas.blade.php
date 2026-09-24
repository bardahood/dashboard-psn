<div class="bg-white shadow-sm ring-1 ring-gray-950/5 rounded-xl p-6">
    <h3 class="font-semibold text-gray-700 mb-4">Bagian A: Identitas & Klasifikasi Hierarki</h3>

    <form wire:submit="saveIdentitas" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PSN <span class="text-red-500">*</span></label>
            @if ($kunjungan)
                <input type="text" disabled value="{{ $psn?->nama_psn }}" class="block w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-500">
            @else
                <select wire:model.live="identitas.psn_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                    <option value="">-- Pilih PSN --</option>
                    @foreach ($psnOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>
            @endif
            @error('identitas.psn_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Hierarki Tercatat</label>
            <input type="text" disabled value="{{ $psn?->tipe_hierarki ?? '-' }}" class="block w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status PSN Tercatat</label>
            <input type="text" wire:model="identitas.status_psn_tercatat" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kunjungan <span class="text-red-500">*</span></label>
            <input type="date" wire:model="identitas.tanggal_kunjungan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
            @error('identitas.tanggal_kunjungan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Kunjungan</label>
            <input type="text" wire:model="identitas.lokasi_kunjungan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Verifikator</label>
            <select wire:model="identitas.verifikator_id" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                <option value="">-- Pilih PIC --</option>
                @foreach ($picOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Kepatuhan Frekuensi Pelaporan
                <span class="text-xs text-gray-400">(saran otomatis, dapat diubah sesuai temuan lapangan)</span>
            </label>
            <select wire:model="identitas.kepatuhan_frekuensi_pelaporan" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm">
                <option value="">-- Pilih --</option>
                <option value="Sesuai">Sesuai</option>
                <option value="Tidak Sesuai">Tidak Sesuai</option>
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tim Verifikator Tambahan</label>
            <textarea wire:model="identitas.tim_verifikator_tambahan" rows="2" class="block w-full rounded-lg border-gray-300 transition-colors shadow-sm text-sm"></textarea>
        </div>

        <div class="sm:col-span-2 flex justify-end">
            <button type="submit" class="rounded-lg bg-blue-800 text-white shadow-sm hover:shadow transition-all px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Bagian B &rarr;
            </button>
        </div>
    </form>
</div>
