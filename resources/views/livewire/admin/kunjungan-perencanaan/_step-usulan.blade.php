<div class="bg-white shadow rounded-lg p-6">
    <h3 class="font-semibold text-gray-700 mb-4">Data Usulan PSN</h3>

    <form wire:submit="saveUsulan" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Usulan PSN <span class="text-red-500">*</span></label>
            <textarea wire:model="usulan.nama_usulan_psn" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
            @error('usulan.nama_usulan_psn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Kode Usulan</label>
            <input type="text" wire:model="usulan.nomor_kode_usulan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kunjungan <span class="text-red-500">*</span></label>
            <input type="date" wire:model="usulan.tanggal_kunjungan" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
            @error('usulan.tanggal_kunjungan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Klaster</label>
            <select wire:model="usulan.klaster_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih Klaster --</option>
                @foreach ($klasterOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
            <select wire:model="usulan.provinsi_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih Provinsi --</option>
                @foreach ($provinsiOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pengusul</label>
            <select wire:model="usulan.pengusul_instansi_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih Instansi --</option>
                @foreach ($instansiOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Jenis Pengusul
                <span class="text-xs text-gray-400">(menentukan kriteria P4/P5/P6 mana yang berlaku)</span>
            </label>
            <select wire:model.live="usulan.jenis_pengusul" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih --</option>
                <option value="KL">Kementerian/Lembaga</option>
                <option value="Pemda">Pemerintah Daerah</option>
                <option value="BUMN & Swasta">BUMN & Swasta</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Detail</label>
            <input type="text" wire:model="usulan.lokasi_detail" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Indikasi Pendanaan</label>
            <input type="text" wire:model="usulan.indikasi_pendanaan" placeholder="APBN/APBD/BUMN/Swasta/KPBU" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Proyek (Rp)</label>
            <input type="number" step="0.01" wire:model="usulan.nilai_proyek_rp" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Verifikator</label>
            <select wire:model="usulan.verifikator_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Pilih PIC --</option>
                @foreach ($picOptions as $id => $nama)
                    <option value="{{ $id }}">{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">KPU Terkait</label>
            <input type="text" wire:model="usulan.kpu_terkait" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                PSN Terkait
                <span class="text-xs text-gray-400">(kosongkan bila usulan baru, belum tercatat di daftar PSN)</span>
            </label>
            <select wire:model="usulan.psn_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">-- Belum Tercatat --</option>
                @foreach ($psnOptions as $id => $nama)
                    <option value="{{ $id }}">{{ \Illuminate\Support\Str::limit($nama, 60) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end pb-1.5">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="isInfrastruktur" class="rounded border-gray-300">
                Usulan ini termasuk proyek infrastruktur
                <span class="text-xs text-gray-400">(menentukan kriteria K3/K4 mana yang berlaku)</span>
            </label>
        </div>

        <div class="sm:col-span-2 flex justify-end">
            <button type="submit" class="rounded-md bg-blue-800 text-white px-4 py-2 text-sm hover:bg-blue-700">
                Simpan &amp; Lanjut ke Hasil PMO &rarr;
            </button>
        </div>
    </form>
</div>
